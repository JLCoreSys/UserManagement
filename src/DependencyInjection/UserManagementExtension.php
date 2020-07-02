<?php
/**
 * CoreSystems (c) 2020
 * Author: Josh McCreight<jmccreight@shaw.ca>
 */

declare( strict_types = 1 );

namespace CoreSys\UserManagement\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

/**
 * Class UserManagementExtension
 * @package CoreSys\UserManagement\DependencyInjection
 */
class UserManagementExtension extends Extension
{
    /**
     * @param array            $configs
     * @param ContainerBuilder $container
     * @throws \Exception
     */
    public function load( array $configs, ContainerBuilder $container )
    {
        $configuration = new Configuration();
        $this->processConfiguration( $configuration, $configs );

        $loader = new YamlFileLoader( $container, new FileLocator(
            implode( DIRECTORY_SEPARATOR, [
                __DIR__,
                '..',
                'Resources',
                'config'
            ] )
        ) );

        $loader->load( 'services.yaml' );
    }

}