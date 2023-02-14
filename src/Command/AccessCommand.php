<?php

/**
 * CoreSystems (c) 2023
 * Author: Josh McCreight<jmccreight@shaw.ca>
 */

declare(strict_types=1);

namespace CoreSys\UserManagement\Command;

use CoreSys\UserManagement\Repository\AccessRepository;
use Symfony\Component\Console\Command\Command;

class AccessCommand extends Command
{
    public function __construct(
        private readonly AccessRepository $accessRepository,
    ) {
        parent::__construct();
    }
}
