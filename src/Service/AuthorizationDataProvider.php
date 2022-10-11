<?php

declare(strict_types=1);

namespace Dbp\Relay\CoreConnectorTextfileBundle\Service;

use Dbp\Relay\CoreBundle\Authorization\AuthorizationDataProviderInterface;
use Dbp\Relay\CoreBundle\Helpers\Tools;
use Dbp\Relay\CoreConnectorTextfileBundle\DependencyInjection\Configuration;

class AuthorizationDataProvider implements AuthorizationDataProviderInterface
{
    /** @var array[] */
    private $groups;

    /** @var array[] */
    private $roles;

    public function __construct()
    {
        $this->groups = [];
        $this->roles = [];
    }

    public function setConfig(array $config)
    {
        foreach ($config[Configuration::GROUPS_ATTRIBUTE] as $group) {
            $members = [];
            foreach ($group[Configuration::GROUP_MEMBERS_ATTRIBUTE] as $groupMember) {
                $members[] = $groupMember[Configuration::NAME_ATTRIBUTE];
            }
            if (!empty($members)) {
                $this->groups[$group[Configuration::NAME_ATTRIBUTE]] = $members;
            }
        }

        foreach ($config[Configuration::ROLES_ATTRIBUTE] as $role) {
            $groups = [];
            foreach ($role[Configuration::GROUPS_ATTRIBUTE] as $roleGroups) {
                $groups[] = $roleGroups[Configuration::NAME_ATTRIBUTE];
            }
            if (!empty($groups)) {
                $this->roles[$role[Configuration::NAME_ATTRIBUTE]] = $groups;
            }
        }
    }

    public function getAvailableRoles(): array
    {
        return array_keys($this->roles);
    }

    public function getAvailableAttributes(): array
    {
        return [];
    }

    public function getUserData(string $userId, array &$userRoles, array &$userAttributes): void
    {
        if (Tools::isNullOrEmpty($userId) === false) {
            $groupsOfUser = [];
            foreach ($this->groups as $groupName => $groupMembers) {
                if (in_array($userId, $groupMembers, true)) {
                    $groupsOfUser[] = $groupName;
                }
            }

            if (!empty($groupsOfUser)) {
                foreach ($this->roles as $roleName => $roleGroups) {
                    if (!empty(array_intersect($roleGroups, $groupsOfUser))) {
                        $userRoles[] = $roleName;
                    }
                }
            }
        }
    }
}
