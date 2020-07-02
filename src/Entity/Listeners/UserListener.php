<?php
/**
 * CoreSystems (c) 2020
 * Author: Josh McCreight<jmccreight@shaw.ca>
 */

declare( strict_types = 1 );

namespace CoreSys\UserManagement\Entity\Listeners;

use CoreSys\UserManagement\Entity\User;
use CoreSys\UserManagement\Manager\UserManager;
use Doctrine\Common\EventArgs;

/**
 * Class UserListener
 * @package CoreSys\UserManagement\Entity\Listeners
 */
class UserListener extends AbstractListener
{
    /**
     * UserListener constructor.
     * @param UserManager $manager
     */
    public function __construct( UserManager $manager )
    {
        $this->setManager( $manager );
    }

    /**
     * @param User      $user
     * @param EventArgs $args
     */
    public function prePersist( User $user, EventArgs $args )
    {
        $this->getManager()->update( $user );
    }
}