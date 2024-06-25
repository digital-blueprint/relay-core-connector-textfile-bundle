<?php

declare(strict_types=1);

namespace Dbp\Relay\CoreConnectorTextfileBundle\Tests;

use Dbp\Relay\CoreBundle\TestUtils\TestAuthorizationService;
use Dbp\Relay\CoreBundle\User\UserAttributeException;
use Dbp\Relay\CoreConnectorTextfileBundle\Service\AuthorizationService;
use Dbp\Relay\CoreConnectorTextfileBundle\Service\UserAttributeProvider;
use PHPUnit\Framework\TestCase;

class Test extends TestCase
{
    private UserAttributeProvider $attributeProvider;
    private AuthorizationService $auth;

    protected function setUp(): void
    {
        parent::setUp();

        $this->auth = new AuthorizationService();
        TestAuthorizationService::setUp($this->auth, 'testuser', ['IS_USER' => false]);
        $this->attributeProvider = new UserAttributeProvider($this->auth);
        $this->attributeProvider->setConfig(self::createAuthorizationConfig());
    }

    public function testAvailableAttributes(): void
    {
        $this->assertSame(['ROLE_USER', 'ROLE_ADMIN', 'VALUE', 'VALUE_2', 'VALUES', 'VALUES_2'],
            $this->attributeProvider->getAvailableAttributes());
        foreach (['ROLE_USER', 'ROLE_ADMIN', 'VALUE', 'VALUE_2', 'VALUES', 'VALUES_2'] as $attribute) {
            $this->assertTrue($this->attributeProvider->hasUserAttribute($attribute));
        }
    }

    public function testNotAvailable(): void
    {
        $this->assertFalse($this->attributeProvider->hasUserAttribute('ROLE_SOMETHING'));

        $this->expectException(UserAttributeException::class);
        $this->attributeProvider->getUserAttribute('user1', 'ROLE_SOMETHING');
    }

    private function assertAttributeSame($expected, $userIdentifier, $attribute): void
    {
        $this->assertTrue($this->attributeProvider->hasUserAttribute($attribute));
        $this->assertContains($attribute, $this->attributeProvider->getAvailableAttributes());
        $this->assertSame($expected, $this->attributeProvider->getUserAttributes($userIdentifier)[$attribute]);
        $this->assertSame($expected, $this->attributeProvider->getUserAttribute($userIdentifier, $attribute));
    }

    public function testRoles(): void
    {
        $this->assertAttributeSame(true, 'user1', 'ROLE_USER');
        $this->assertAttributeSame(true, 'user2', 'ROLE_USER');
        $this->assertAttributeSame(false, 'admin', 'ROLE_USER');
        $this->assertAttributeSame(null, 'user1', 'ROLE_ADMIN');
        $this->assertAttributeSame(null, 'user2', 'ROLE_ADMIN');
        $this->assertAttributeSame(true, 'admin', 'ROLE_ADMIN');

        // not configured user: default values expected
        $this->assertAttributeSame(false, 'other', 'ROLE_USER');
        $this->assertAttributeSame(null, 'other', 'ROLE_ADMIN');

        // null user (e.g. for system account users): default values expected
        $this->assertAttributeSame(false, null, 'ROLE_USER');
        $this->assertAttributeSame(null, null, 'ROLE_ADMIN');
    }

    public function testScalarValue(): void
    {
        $this->assertAttributeSame(1, 'user1', 'VALUE');
        $this->assertAttributeSame(1, 'user2', 'VALUE');
        $this->assertAttributeSame(2, 'admin', 'VALUE');
        $this->assertAttributeSame(1, 'user1', 'VALUE_2');
        $this->assertAttributeSame(1, 'user2', 'VALUE_2');
        $this->assertAttributeSame(0, 'admin', 'VALUE_2');

        // not configured user: default values expected
        $this->assertAttributeSame(null, 'other', 'VALUE');
        $this->assertAttributeSame(0, 'other', 'VALUE_2');

        // null user (e.g. for system account users): default values expected
        $this->assertAttributeSame(null, null, 'VALUE');
        $this->assertAttributeSame(0, null, 'VALUE_2');
    }

    public function testArrayValue(): void
    {
        $this->assertAttributeSame([1], 'user1', 'VALUES');
        $this->assertAttributeSame([1], 'user2', 'VALUES');
        $this->assertAttributeSame([2], 'admin', 'VALUES');
        $this->assertAttributeSame([1], 'user1', 'VALUES_2');
        $this->assertAttributeSame([1], 'user2', 'VALUES_2');
        $this->assertAttributeSame([1, 2, 3], 'admin', 'VALUES_2');

        // not configured user: default values expected
        $this->assertAttributeSame([], 'other', 'VALUES');
        $this->assertAttributeSame([1, 2, 3], 'other', 'VALUES_2');

        // null user (e.g. for system account users): default values expected
        $this->assertAttributeSame([], null, 'VALUES');
        $this->assertAttributeSame([1, 2, 3], null, 'VALUES_2');
    }

    public function testValueExpression(): void
    {
        // 'testuser' is not part of the group 'USERS'
        $this->assertAttributeSame(false, 'testuser', 'ROLE_USER');

        // however, if we give them the required user attribute
        TestAuthorizationService::setUp($this->auth, 'testuser', ['IS_USER' => true]);

        // the value expression will evaluate to 'true'
        $this->assertAttributeSame(true, 'testuser', 'ROLE_USER');
    }

    private static function createAuthorizationConfig(): array
    {
        $config = [];
        $config['groups'] = [
            [
                'name' => 'USERS',
                'users' => [
                    'user1',
                    'user2',
                ],
            ],
        ];
        $config['attributes'] = [
            [
                'name' => 'ROLE_USER',
                'array' => false,
                'value_expression' => 'user.get("IS_USER")',
            ],
            [
                'name' => 'ROLE_ADMIN',
                'array' => false,
            ],
            [
                'name' => 'VALUE',
                'array' => false,
                // default default value: null
            ],
            [
                'name' => 'VALUE_2',
                'default_value' => 0,
                'array' => false,
            ],
            [
                'name' => 'VALUES',
                'array' => true,
                // default default value: []
            ],
            [
                'name' => 'VALUES_2',
                'array' => true,
                'default_values' => [1, 2, 3],
            ],
        ];
        $config['attribute_mapping'] = [
            [
                'name' => 'ROLE_USER',
                'groups' => [
                    'USERS',
                ],
                'value' => true,
            ],
            [
                'name' => 'ROLE_ADMIN',
                'users' => [
                    'admin',
                ],
                'value' => true,
            ],
            [
                'name' => 'VALUE',
                'groups' => [
                    'USERS',
                ],
                'value' => 1,
            ],
            [
                'name' => 'VALUE',
                'users' => [
                    'admin',
                ],
                'value' => 2,
            ],
            [
                'name' => 'VALUE_2',
                'groups' => [
                    'USERS',
                ],
                'value' => 1,
            ],
            [
                'name' => 'VALUES',
                'groups' => [
                    'USERS',
                ],
                'values' => [1],
            ],
            [
                'name' => 'VALUES',
                'users' => [
                    'admin',
                ],
                'values' => [2],
            ],
            [
                'name' => 'VALUES_2',
                'groups' => [
                    'USERS',
                ],
                'values' => [1],
            ],
        ];

        return $config;
    }
}
