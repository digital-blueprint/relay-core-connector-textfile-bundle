<?php

declare(strict_types=1);

namespace Dbp\Relay\CoreConnectorTextfileBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public const ROLES_ATTRIBUTE = 'roles';
    public const NAME_ATTRIBUTE = 'name';

    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('dbp_relay_auth_connector_textfile');
        $treeBuilder->getRootNode()
            ->children()
                ->arrayNode(self::ROLES_ATTRIBUTE)
                    ->arrayPrototype()
                        ->children()
                            ->scalarNode(self::NAME_ATTRIBUTE)
                                ->info('The name of the role')
                                ->example('ROLE_VIEWER')
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
