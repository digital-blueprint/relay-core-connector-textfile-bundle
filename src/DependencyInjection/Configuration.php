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
    public const ROLES_ATTRIBUTE = 'roles';

    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('dbp_relay_core_connector_textfile');
        $treeBuilder->getRootNode()
            ->children()
                ->arrayNode(self::GROUPS_ATTRIBUTE)
                    ->arrayPrototype()
                        ->children()
                            ->scalarNode(self::NAME_ATTRIBUTE)
                            ->end()
                            ->arrayNode(self::GROUP_MEMBERS_ATTRIBUTE)
                                ->arrayPrototype()
                                    ->children()
                                        ->scalarNode(self::NAME_ATTRIBUTE)
                                        ->end()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode(self::ROLES_ATTRIBUTE)
                    ->arrayPrototype()
                        ->children()
                            ->scalarNode(self::NAME_ATTRIBUTE)
                            ->end()
                            ->arrayNode(self::GROUPS_ATTRIBUTE)
                                ->arrayPrototype()
                                    ->children()
                                        ->scalarNode(self::NAME_ATTRIBUTE)
                                        ->end()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
