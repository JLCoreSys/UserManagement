<?php

/**
 * CoreSystems (c) 2023
 * Author: Josh McCreight<jmccreight@shaw.ca>
 */

declare(strict_types=1);

namespace CoreSys\UserManagement;

use CoreSys\UserManagement\DependencyInjection\Compiler\AccessCompilerPass;
use CoreSys\UserManagement\DependencyInjection\Compiler\RolesCompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * Class UserManagementBundle
 * @package CoreSys\UserManagement
 */
class UserManagementBundle extends Bundle
{
    public function build(ContainerBuilder $builder)
    {
        parent::build($builder);
        $builder->addCompilerPass(new RolesCompilerPass);
        $builder->addCompilerPass(new AccessCompilerPass);
    }
}
