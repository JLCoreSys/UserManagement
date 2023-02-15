<?php

/**
 * CoreSystems (c) 2023
 * Author: Josh McCreight<jmccreight@shaw.ca>
 */

declare(strict_types=1);

namespace CoreSys\UserManagement\DependencyInjection\Compiler;

use LogicException;
use RuntimeException;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\DependencyInjection\Argument\IteratorArgument;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class RolesCompilerPass implements CompilerPassInterface
{
    private array $expressions = [];
    private array $requestMatchers = [];

    public function process(ContainerBuilder $container)
    {
        if (!$container->hasParameter('coresys_user_management.security.role_hierarchy')) {
            return;
        }

        // handle setting the roles
        $roleHierarchy = $container->getParameter('coresys_user_management.security.role_hierarchy');
        $container->setParameter('security.role_hierarchy.roles', $roleHierarchy);
    }
}
