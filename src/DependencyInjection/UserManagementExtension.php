<?php

/**
 * CoreSystems (c) 2023
 * Author: Josh McCreight<jmccreight@shaw.ca>
 */

declare(strict_types=1);

namespace CoreSys\UserManagement\DependencyInjection;

use CoreSys\UserManagement\UserManagementBundle;
use Exception;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Yaml\Yaml;

/**
 * Class UserManagementExtension
 * @package CoreSys\UserManagement\DependencyInjection
 */
class UserManagementExtension extends Extension
{
    public const FILENAME = 'coresys.yaml';

    /**
     * @param array            $configs
     * @param ContainerBuilder $container
     * @throws \Exception
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        UserManagementBundle::install();

        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new YamlFileLoader(
            $container,
            new FileLocator(
                implode(DIRECTORY_SEPARATOR, [
                    __DIR__,
                    '..',
                    'Resources',
                    'config'
                ])
            )
        );

        $loader->load('services.yaml');

        $fixtures = $config['fixtures'] ?? [];
        $container->setParameter('coresys_user_management.fixtures.users', $fixtures['users'] ?? []);
        $container->setParameter('coresys_user_management.fixtures.roles', $fixtures['roles'] ?? []);
        $container->setParameter('coresys_user_management.fixtures.access', $fixtures['access'] ?? []);
    }
}
