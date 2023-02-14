<?php

/**
 * CoreSystems (c) 2023
 * Author: Josh McCreight<jmccreight@shaw.ca>
 */

declare(strict_types=1);

namespace CoreSys\UserManagement\DependencyInjection;

use CoreSys\UserManagement\UserManagementBundle;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * Class Configuration
 * @package CoreSys\UserManagement\DependencyInjection
 */
class Configuration implements ConfigurationInterface
{
    /**
     * @return TreeBuilder
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder(UserManagementBundle::PACKAGE_NAME);
        $rootNode = $treeBuilder->getRootNode();

        $rootNode
            ->children()
            ->arrayNode('fixtures')
                ->fixXmlConfig('role')
                ->children()
                    // users
                    ->arrayNode('users')
                        ->arrayPrototype()
                            ->children()
                                ->scalarNode('email')->end()
                                ->scalarNode('password')->end()
                                ->arrayNode('roles')
                                    ->scalarPrototype()->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                    // roles
                    ->arrayNode('roles')
                        ->arrayPrototype()
                            ->children()
                                ->scalarNode('name')->cannotBeEmpty()->end()
                                ->booleanNode('mandatory')->defaultFalse()->end()
                                ->booleanNode('enabled')->defaultTrue()->end()
                                ->booleanNode('switch')->defaultFalse()->end()
                                ->arrayNode('inherits')
                                    ->scalarPrototype()->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                    // access
                    ->arrayNode('access')
                        ->cannotBeOverwritten()
                        ->arrayPrototype()
                            ->children()
                                ->scalarNode('request_matcher')->defaultNull()->end()
                                ->scalarNode('required_channel')->defaultNull()->end()
                                ->scalarNode('path')
                                    ->defaultNull()
                                    ->info('user the urldecoded format')
                                    ->example('^/path_to_resource/')
                                ->end()
                                ->scalarNode('host')->defaultNull()->end()
                                ->integerNode('port')->defaultNull()->end()
                                ->arrayNode('ips')
                                    ->beforeNormalization()->ifString()->then(function ($v) {
                                        return [$v];
                                    })->end()
                                    ->prototype('scalar')->end()
                                ->end()
                                ->arrayNode('attributes')
                                    ->useAttributeAsKey('key')
                                    ->prototype('scalar')->end()
                                ->end()
                                ->scalarNode('route')->defaultNull()->end()
                                ->arrayNode('methods')
                                    ->beforeNormalization()->ifString()->then(function ($v) {
                                        return preg_split('/\s*,\s*/', $v);
                                    })->end()
                                    ->prototype('scalar')->end()
                                ->end()
                                ->scalarNode('allow_if')->defaultNull()->end()
                            ->end()
                            ->children()
                                ->booleanNode('public')->defaultTrue()->end()
                                ->booleanNode('mandatory')->defaultFalse()->end()
                                ->booleanNode('enabled')->defaultTrue()->end()
                                ->arrayNode('roles')
                                    ->beforeNormalization()->ifString()->then(function ($v) {
                                        return preg_split('/\s*,\s*/', $v);
                                    })->end()
                                    ->prototype('scalar')->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ->end();

        return $treeBuilder;
    }
}
