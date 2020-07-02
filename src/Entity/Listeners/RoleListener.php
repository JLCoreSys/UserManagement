<?php
/**
 * CoreSystems (c) 2020
 * Author: Josh McCreight<jmccreight@shaw.ca>
 */

declare( strict_types = 1 );

namespace CoreSys\UserManagement\Entity\Listeners;

use CoreSys\UserManagement\Entity\Role;
use CoreSys\UserManagement\Manager\RoleManager;
use Doctrine\Common\EventArgs;

/**
 * Class RoleListener
 * @package CoreSys\UserManagement\Entity\Listeners
 */
class RoleListener extends AbstractListener
{
    /**
     * RoleListener constructor.
     * @param RoleManager $manager
     */
    public function __construct( RoleManager $manager )
    {
        $this->setManager( $manager );
    }

    /**
     * @param Role      $role
     * @param EventArgs $args
     */
    public function prePersist( Role $role, EventArgs $args )
    {
        $this->getManager()->update( $role );
    }
}