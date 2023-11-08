<?php

declare(strict_types=1);

namespace Dbp\Relay\CoreConnectorTextfileBundle\Tests;

use Dbp\Relay\CoreConnectorTextfileBundle\Service\AuthorizationDataProvider;
use PHPUnit\Framework\TestCase;

class Test extends TestCase
{
    /**
     * @var AuthorizationDataProvider
     */
    private $authorizationDataProvider;

    protected function setUp(): void
    {
        parent::setUp();

        $this->authorizationDataProvider = new AuthorizationDataProvider();
        $this->authorizationDataProvider->setConfig(self::createAuthorizationConfig());
    }

    public function testAvailableAttributes(): void
    {
        $this->assertEquals(['ROLE_USER', 'ROLE_ADMIN', 'VALUE', 'VALUE_2', 'VALUES', 'VALUES_2'],
            $this->authorizationDataProvider->getAvailableAttributes());
    }

    public function testRoles(): void
    {
        $this->assertEquals(true, $this->authorizationDataProvider->getUserAttributes('user1')['ROLE_USER']);
        $this->assertEquals(true, $this->authorizationDataProvider->getUserAttributes('user2')['ROLE_USER']);
        $this->assertEquals(false, $this->authorizationDataProvider->getUserAttributes('admin')['ROLE_USER']);
        $this->assertEquals(false, $this->authorizationDataProvider->getUserAttributes('user1')['ROLE_ADMIN']);
        $this->assertEquals(false, $this->authorizationDataProvider->getUserAttributes('user2')['ROLE_ADMIN']);
        $this->assertEquals(true, $this->authorizationDataProvider->getUserAttributes('admin')['ROLE_ADMIN']);

        // not configured user: default values expected
        $this->assertEquals(false, $this->authorizationDataProvider->getUserAttributes('other')['ROLE_USER']);
        $this->assertEquals(false, $this->authorizationDataProvider->getUserAttributes('other')['ROLE_ADMIN']);

        // null user (e.g. for system account users): default values expected
        $this->assertEquals(false, $this->authorizationDataProvider->getUserAttributes(null)['ROLE_USER']);
        $this->assertEquals(false, $this->authorizationDataProvider->getUserAttributes(null)['ROLE_ADMIN']);
    }

    public function testScalarValue(): void
    {
        $this->assertEquals(1, $this->authorizationDataProvider->getUserAttributes('user1')['VALUE']);
        $this->assertEquals(1, $this->authorizationDataProvider->getUserAttributes('user2')['VALUE']);
        $this->assertEquals(2, $this->authorizationDataProvider->getUserAttributes('admin')['VALUE']);
        $this->assertEquals(1, $this->authorizationDataProvider->getUserAttributes('user1')['VALUE_2']);
        $this->assertEquals(1, $this->authorizationDataProvider->getUserAttributes('user2')['VALUE_2']);
        $this->assertEquals(0, $this->authorizationDataProvider->getUserAttributes('admin')['VALUE_2']);

        // not configured user: default values expected
        $this->assertEquals(null, $this->authorizationDataProvider->getUserAttributes('other')['VALUE']);
        $this->assertEquals(0, $this->authorizationDataProvider->getUserAttributes('other')['VALUE_2']);

        // null user (e.g. for system account users): default values expected
        $this->assertEquals(null, $this->authorizationDataProvider->getUserAttributes(null)['VALUE']);
        $this->assertEquals(0, $this->authorizationDataProvider->getUserAttributes(null)['VALUE_2']);
    }

    public function testArrayValue(): void
    {
        $this->assertEquals([1], $this->authorizationDataProvider->getUserAttributes('user1')['VALUES']);
        $this->assertEquals([1], $this->authorizationDataProvider->getUserAttributes('user2')['VALUES']);
        $this->assertEquals([2], $this->authorizationDataProvider->getUserAttributes('admin')['VALUES']);
        $this->assertEquals([1], $this->authorizationDataProvider->getUserAttributes('user1')['VALUES_2']);
        $this->assertEquals([1], $this->authorizationDataProvider->getUserAttributes('user2')['VALUES_2']);
        $this->assertEquals([1, 2, 3], $this->authorizationDataProvider->getUserAttributes('admin')['VALUES_2']);

        // not configured user: default values expected
        $this->assertEquals([], $this->authorizationDataProvider->getUserAttributes('other')['VALUES']);
        $this->assertEquals([1, 2, 3], $this->authorizationDataProvider->getUserAttributes('other')['VALUES_2']);

        // null user (e.g. for system account users): default values expected
        $this->assertEquals([], $this->authorizationDataProvider->getUserAttributes(null)['VALUES']);
        $this->assertEquals([1, 2, 3], $this->authorizationDataProvider->getUserAttributes(null)['VALUES_2']);
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
