<?php

namespace Wexample\SymfonyWex\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Wexample\PhpWex\Const\Globals;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('wexample_symfony_wex');

        $treeBuilder->getRootNode()
            ->children()
            ->scalarNode('binary')
            ->defaultValue(Globals::CORE_COMMAND_NAME)
            ->info('Name resolved against the PATH, or absolute path to a wex executable.')
            ->end()
            ->end();

        $treeBuilder->getRootNode()
            ->children()
            ->scalarNode('working_directory')
            ->defaultNull()
            ->info('Directory commands run from, which is what makes the app commands reachable. Defaults to the project directory.')
            ->end()
            ->end();

        $treeBuilder->getRootNode()
            ->children()
            ->arrayNode('agent_server')
            ->info('The wex agent server turns are run by. Left out, nothing here can run one.')
            ->children()
            ->scalarNode('url')
            ->defaultNull()
            ->info('Base address of the server, without a trailing slash.')
            ->end()
            ->scalarNode('token')
            ->defaultNull()
            ->info('Bearer every request to it carries.')
            ->end()
            ->scalarNode('app_path')
            ->defaultNull()
            ->info('Path of the app the server was started on, the one it answers for when a request names none. Defaults to the project directory.')
            ->end()
            ->end()
            ->end()
            ->end();

        $treeBuilder->getRootNode()
            ->children()
            ->floatNode('timeout')
            ->defaultNull()
            ->info('Seconds before a command is terminated. Null waits indefinitely.')
            ->end()
            ->end();

        return $treeBuilder;
    }
}
