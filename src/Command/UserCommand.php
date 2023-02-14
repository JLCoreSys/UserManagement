<?php

/**
 * CoreSystems (c) 2023
 * Author: Josh McCreight<jmccreight@shaw.ca>
 */

declare(strict_types=1);

namespace CoreSys\UserManagement\Command;

use CoreSys\UserManagement\Repository\RoleRepository;
use CoreSys\UserManagement\Repository\UserRepository;
use Symfony\Component\Console\Command\Command;

class UserCommand extends Command
{
    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly RoleRepository $roleRepository,
    ) {
        parent::__construct();
    }
}
