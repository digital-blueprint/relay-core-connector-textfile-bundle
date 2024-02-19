<?php

declare(strict_types=1);

namespace Dbp\Relay\CoreConnectorTextfileBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public const NAME_ATTRIBUTE = 'name';
    public const GROUPS_ATTRIBUTE = 'groups';
    public const GROUP_MEMBERS_ATTRIBUTE = 'members';
    public const ATTRIBUTES_ATTRIBUTE = 'attributes';
    public const IS_ARRAY_ATTRIBUTE = 'array';
    public const DEFAULT_VALUE_ATTRIBUTE = 'default_value';
    public const DEFAULT_VALUES_ATTRIBUTE = 'default_values';
    public const ATTRIBUTE_MAPPING_ATTRIBUTE = 'attribute_mapping';
    public const USERS_ATTRIBUTE = 'users';
    public const VALUE_ATTRIBUTE = 'value';
    public const VALUES_ATTRIBUTE = 'values';
    public const VALUE_EXPRESSION_ATTRIBUTE = 'value_expression';

    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('dbp_relay_core_connector_textfile');
        $treeBuilder->getRootNode()
            ->children()
                ->arrayNode(self::GROUPS_ATTRIBUTE)
                    ->arrayPrototype()
                        ->children()
                            ->scalarNode(self::NAME_ATTRIBUTE)->end()
                            ->arrayNode(self::USERS_ATTRIBUTE)
                                ->scalarPrototype()->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode(self::ATTRIBUTES_ATTRIBUTE)
                    ->arrayPrototype()
                        ->children()
                            ->scalarNode(self::NAME_ATTRIBUTE)->end()
                            ->booleanNode(self::IS_ARRAY_ATTRIBUTE)
                                ->defaultFalse()
                            ->end()
                            ->scalarNode(self::DEFAULT_VALUE_ATTRIBUTE)->end()
                            ->arrayNode(self::DEFAULT_VALUES_ATTRIBUTE)
                                ->scalarPrototype()->end()
                            ->end()
                            ->scalarNode(self::VALUE_EXPRESSION_ATTRIBUTE)->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode(self::ATTRIBUTE_MAPPING_ATTRIBUTE)
                    ->arrayPrototype()
                        ->children()
                            ->scalarNode(self::NAME_ATTRIBUTE)->end()
                            ->arrayNode(self::GROUPS_ATTRIBUTE)
                                ->scalarPrototype()->end()
                            ->end()
                            ->arrayNode(self::USERS_ATTRIBUTE)
                                ->scalarPrototype()->end()
                            ->end()
                            ->scalarNode(self::VALUE_ATTRIBUTE)->end()
                            ->arrayNode(self::VALUES_ATTRIBUTE)
                                ->scalarPrototype()->end()
                            ->end()
                            ->scalarNode(self::VALUE_EXPRESSION_ATTRIBUTE)->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
