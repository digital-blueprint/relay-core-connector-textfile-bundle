<?php

declare(strict_types=1);

namespace Dbp\Relay\CoreConnectorTextfileBundle\Tests;

use Dbp\Relay\CoreBundle\TestUtils\TestAuthorizationService;
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
    }

    public function testRoles(): void
    {
        $this->assertSame(true, $this->attributeProvider->getUserAttributes('user1')['ROLE_USER']);
        $this->assertSame(true, $this->attributeProvider->getUserAttributes('user2')['ROLE_USER']);
        $this->assertSame(false, $this->attributeProvider->getUserAttributes('admin')['ROLE_USER']);
        $this->assertSame(null, $this->attributeProvider->getUserAttributes('user1')['ROLE_ADMIN']);
        $this->assertSame(null, $this->attributeProvider->getUserAttributes('user2')['ROLE_ADMIN']);
        $this->assertSame(true, $this->attributeProvider->getUserAttributes('admin')['ROLE_ADMIN']);

        // not configured user: default values expected
        $this->assertSame(false, $this->attributeProvider->getUserAttributes('other')['ROLE_USER']);
        $this->assertSame(null, $this->attributeProvider->getUserAttributes('other')['ROLE_ADMIN']);

        // null user (e.g. for system account users): default values expected
        $this->assertSame(false, $this->attributeProvider->getUserAttributes(null)['ROLE_USER']);
        $this->assertSame(null, $this->attributeProvider->getUserAttributes(null)['ROLE_ADMIN']);
    }

    public function testScalarValue(): void
    {
        $this->assertSame(1, $this->attributeProvider->getUserAttributes('user1')['VALUE']);
        $this->assertSame(1, $this->attributeProvider->getUserAttributes('user2')['VALUE']);
        $this->assertSame(2, $this->attributeProvider->getUserAttributes('admin')['VALUE']);
        $this->assertSame(1, $this->attributeProvider->getUserAttributes('user1')['VALUE_2']);
        $this->assertSame(1, $this->attributeProvider->getUserAttributes('user2')['VALUE_2']);
        $this->assertSame(0, $this->attributeProvider->getUserAttributes('admin')['VALUE_2']);

        // not configured user: default values expected
        $this->assertSame(null, $this->attributeProvider->getUserAttributes('other')['VALUE']);
        $this->assertSame(0, $this->attributeProvider->getUserAttributes('other')['VALUE_2']);

        // null user (e.g. for system account users): default values expected
        $this->assertSame(null, $this->attributeProvider->getUserAttributes(null)['VALUE']);
        $this->assertSame(0, $this->attributeProvider->getUserAttributes(null)['VALUE_2']);
    }

    public function testArrayValue(): void
    {
        $this->assertSame([1], $this->attributeProvider->getUserAttributes('user1')['VALUES']);
        $this->assertSame([1], $this->attributeProvider->getUserAttributes('user2')['VALUES']);
        $this->assertSame([2], $this->attributeProvider->getUserAttributes('admin')['VALUES']);
        $this->assertSame([1], $this->attributeProvider->getUserAttributes('user1')['VALUES_2']);
        $this->assertSame([1], $this->attributeProvider->getUserAttributes('user2')['VALUES_2']);
        $this->assertSame([1, 2, 3], $this->attributeProvider->getUserAttributes('admin')['VALUES_2']);

        // not configured user: default values expected
        $this->assertSame([], $this->attributeProvider->getUserAttributes('other')['VALUES']);
        $this->assertSame([1, 2, 3], $this->attributeProvider->getUserAttributes('other')['VALUES_2']);

        // null user (e.g. for system account users): default values expected
        $this->assertSame([], $this->attributeProvider->getUserAttributes(null)['VALUES']);
        $this->assertSame([1, 2, 3], $this->attributeProvider->getUserAttributes(null)['VALUES_2']);
    }

    public function testValueExpression(): void
    {
        // 'testuser' is not part of the group 'USERS'
        $this->assertFalse($this->attributeProvider->getUserAttributes('testuser')['ROLE_USER']);

        // however, if we give them the required user attribute
        TestAuthorizationService::setUp($this->auth, 'testuser', ['IS_USER' => true]);

        // the value expression will evaluate to 'true'
        $this->assertTrue($this->attributeProvider->getUserAttributes('testuser')['ROLE_USER']);
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
