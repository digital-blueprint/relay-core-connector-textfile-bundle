<?php

declare(strict_types=1);

namespace Dbp\Relay\CoreConnectorTextfileBundle\Service;

use Dbp\Relay\CoreBundle\Authorization\AuthorizationDataProviderInterface;
use Dbp\Relay\CoreBundle\Helpers\Tools;
use Dbp\Relay\CoreConnectorTextfileBundle\DependencyInjection\Configuration;

class AuthorizationDataProvider implements AuthorizationDataProviderInterface
{
    private const GROUP_MEMBERS_ATTRIBUTE = 'members';
    private const GROUP_ATTRIBUTES_ATTRIBUTE = 'attributes';
    private const DEFAULT_VALUE_ATTRIBUTE = 'default_value';
    private const IS_ARRAY_ATTRIBUTE = 'is_array';

    /**
     * Array of available user groups:
     * ['group1' => [self::GROUP_MEMBERS_ATTRIBUTE => ['user1'], self::GROUP_ATTRIBUTES_ATTRIBUTE => ['attr1' => 'value1'], 'group2' => ... ].
     *
     * @var array
     */
    private $groups;

    /**
     * Array of available attributes:
     * ['attr1' => [self::DEFAULT_VALUE_ATTRIBUTE => 'value0', self::IS_ARRAY_ATTRIBUTE => 'false'], 'attr2' => ... ].
     *
     * @var array
     */
    private $attributes;

    public function __construct()
    {
        $this->groups = [];
        $this->attributes = [];
    }

    public function setConfig(array $config)
    {
        $this->loadConfig($config);
    }

    public function getAvailableAttributes(): array
    {
        return array_keys($this->attributes);
    }

    public function getUserAttributes(?string $userIdentifier): array
    {
        $userAttributes = [];

        if (Tools::isNullOrEmpty($userIdentifier) === false) {
            foreach ($this->groups as $group) {
                if (in_array($userIdentifier, $group[self::GROUP_MEMBERS_ATTRIBUTE], true)) {
                    foreach ($group[self::GROUP_ATTRIBUTES_ATTRIBUTE] as $attributeName => $attributeValue) {
                        if (isset($userAttributes[$attributeName]) && $userAttributes[$attributeName] !== $attributeValue) {
                            throw new \RuntimeException(sprintf('conflicting values for attribute \'%s\'', $attributeName));
                        }
                        $userAttributes[$attributeName] = $attributeValue;
                    }
                }
            }
        }
        // set default values for attributes without values
        foreach ($this->attributes as $attributeName => $attributeValue) {
            if (!isset($userAttributes[$attributeName])) {
                $userAttributes[$attributeName] = $attributeValue[self::DEFAULT_VALUE_ATTRIBUTE];
            }
        }

        return $userAttributes;
    }

    private function loadConfig(array $config)
    {
        foreach ($config[Configuration::GROUPS_ATTRIBUTE] as $group) {
            $members = $group[Configuration::USERS_ATTRIBUTE] ?? [];
            if (!empty($members)) {
                $this->groups[$group[Configuration::NAME_ATTRIBUTE]] = [
                    self::GROUP_MEMBERS_ATTRIBUTE => $members,
                    self::GROUP_ATTRIBUTES_ATTRIBUTE => [],
                ];
            }
        }

        $this->attributes = [];
        foreach ($config[Configuration::ATTRIBUTES_ATTRIBUTE] as $attribute) {
            $attributeName = $attribute[Configuration::NAME_ATTRIBUTE];
            if (isset($this->attributes[$attributeName])) {
                throw new \RuntimeException(sprintf('multiple declaration of attribute \'%s\'', $attributeName));
            }
            $isArray = $attribute[Configuration::IS_ARRAY_ATTRIBUTE];
            if ($isArray) {
                $defaultValue = $attribute[Configuration::DEFAULT_VALUES_ATTRIBUTE] ?? [];
            } else {
                $defaultValue = $attribute[Configuration::DEFAULT_VALUE_ATTRIBUTE] ?? null;
            }
            $this->attributes[$attributeName] = [
                self::DEFAULT_VALUE_ATTRIBUTE => $defaultValue,
                self::IS_ARRAY_ATTRIBUTE => $isArray,
            ];
        }

        $mappingIndex = 0;
        foreach ($config[Configuration::ATTRIBUTE_MAPPING_ATTRIBUTE] as $attributeMapping) {
            $attributeName = $attributeMapping[Configuration::NAME_ATTRIBUTE];
            $groupNames = $attributeMapping[Configuration::GROUPS_ATTRIBUTE] ?? [];
            foreach ($groupNames as $groupName) {
                if (!isset($this->groups[$groupName])) {
                    throw new \RuntimeException(sprintf('group \'%s\' not declared in \'%s\' config section', $groupName, Configuration::GROUPS_ATTRIBUTE));
                }
            }

            $users = $attributeMapping[Configuration::USERS_ATTRIBUTE] ?? [];
            if (!empty($users)) {
                // add a new dummy group for each user list
                $groupName = $attributeName.$mappingIndex;
                $this->groups[$groupName] = [
                    self::GROUP_MEMBERS_ATTRIBUTE => $users,
                    self::GROUP_ATTRIBUTES_ATTRIBUTE => [],
                ];
                $groupNames[] = $groupName;
            }

            $attribute = $this->attributes[$attributeName] ?? null;
            if ($attribute === null) {
                throw new \RuntimeException(sprintf('attribute \'%s\' not declared in \'%s\' config section', $attributeName, Configuration::ATTRIBUTES_ATTRIBUTE));
            }

            if ($attribute[self::IS_ARRAY_ATTRIBUTE]) {
                if (isset($attributeMapping[Configuration::VALUE_ATTRIBUTE])) {
                    throw new \RuntimeException(sprintf('scalar value given for array attribute \'%s\'', $attributeName));
                }
                $value = $attributeMapping[Configuration::VALUES_ATTRIBUTE];
            } else {
                // NOTE: symfony automatically provides a default value for prototype array elements, i.e. we have to check, whether the array is non-empty
                if (!empty($attributeMapping[Configuration::VALUES_ATTRIBUTE] ?? [])) {
                    throw new \RuntimeException(sprintf('array value given for scalar attribute \'%s\'', $attributeName));
                } elseif (!isset($attributeMapping[Configuration::VALUE_ATTRIBUTE])) {
                    throw new \RuntimeException(sprintf('no value given for scalar attribute \'%s\'', $attributeName));
                }
                $value = $attributeMapping[Configuration::VALUE_ATTRIBUTE];
                $this->attributes[$attributeName][self::DEFAULT_VALUE_ATTRIBUTE] = $attribute[self::DEFAULT_VALUE_ATTRIBUTE] ?? null;
            }

            foreach ($groupNames as $groupName) {
                $this->groups[$groupName][self::GROUP_ATTRIBUTES_ATTRIBUTE][$attributeName] = $value;
            }

            ++$mappingIndex;
        }
    }
}
