<?php

declare(strict_types=1);

namespace Dbp\Relay\CoreConnectorTextfileBundle\Service;

use Dbp\Relay\CoreBundle\Authorization\AuthorizationDataProviderInterface;
use Dbp\Relay\CoreBundle\Helpers\Tools;
use Dbp\Relay\CoreConnectorTextfileBundle\DependencyInjection\Configuration;
use Symfony\Component\Yaml\Yaml;

class AuthorizationDataProvider implements AuthorizationDataProviderInterface
{
    private const GROUPS_CONFIG_ATTRIBUTE = 'groups';
    private const ROLES_CONFIG_ATTRIBUTE = 'roles';

    /** @var string[] */
    private $availableRoles;

    /** @var string[] */
    private $availableAttributes;

    public function __construct()
    {
        $this->availableRoles = [];
        $this->availableAttributes = [];
    }

    public function setConfig(array $config)
    {
        foreach ($config[Configuration::ROLES_ATTRIBUTE] as $role) {
            $this->availableRoles[] = $role[Configuration::NAME_ATTRIBUTE];
        }
    }

    public function getAvailableRoles(): array
    {
        return $this->availableRoles;
    }

    public function getAvailableAttributes(): array
    {
        return $this->availableAttributes;
    }

    public function getUserData(string $userId, array &$userRoles, array &$userAttributes): void
    {
        if (Tools::isNullOrEmpty($userId) === false) {
            $rolesConfig = Yaml::parseFile(__DIR__.'/../Resources/config/roles.yaml');
            $groups = $rolesConfig[self::GROUPS_CONFIG_ATTRIBUTE];
            $roles = $rolesConfig[self::ROLES_CONFIG_ATTRIBUTE];

            $groupsOfUser = [];
            foreach ($groups as $group) {
                foreach ($group as $groupName => $groupMembers) {
                    if ($groupMembers !== null && in_array($userId, $groupMembers, true)) {
                        $groupsOfUser[] = $groupName;
                    }
                }
            }

            foreach ($roles as $role) {
                foreach ($role as $roleName => $roleGroups) {
                    if ($roleGroups !== null && count(array_intersect($roleGroups, $groupsOfUser)) > 0) {
                        $userRoles[] = $roleName;
                    }
                }
            }
        }
    }
}
