<?php

namespace Wexample\SymfonyWex\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Wexample\SymfonyHelpers\DependencyInjection\AbstractWexampleSymfonyExtension;
use Wexample\SymfonyWex\Interface\FileViewInterface;

class WexampleSymfonyWexExtension extends AbstractWexampleSymfonyExtension
{
    public function load(
        array $configs,
        ContainerBuilder $container
    ): void {
        $this->loadConfig(
            __DIR__,
            $container
        );

        // So a bundle offers a view of some files by implementing the
        // interface, with nothing to add in its own services.yaml.
        $container
            ->registerForAutoconfiguration(FileViewInterface::class)
            ->addTag(FileViewInterface::TAG);

        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $container->setParameter(
            'wexample_symfony_wex.binary',
            $config['binary']
        );

        $container->setParameter(
            'wexample_symfony_wex.working_directory',
            $config['working_directory'] ?? $container->getParameter('kernel.project_dir')
        );

        $container->setParameter(
            'wexample_symfony_wex.timeout',
            $config['timeout']
        );

        $container->setParameter(
            'wexample_symfony_wex.agent_server.url',
            $config['agent_server']['url']
        );

        $container->setParameter(
            'wexample_symfony_wex.agent_server.token',
            $config['agent_server']['token']
        );
    }
}
