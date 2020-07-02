<?php
/**
 * CoreSystems (c) 2020
 * Author: Josh McCreight<jmccreight@shaw.ca>
 */

declare( strict_types = 1 );

namespace CoreSys\UserManagement\Entity\Listeners;

use CoreSys\UserManagement\Entity\Access;
use CoreSys\UserManagement\Manager\AccessManager;
use Doctrine\Common\EventArgs;

/**
 * Class AccessListener
 * @package CoreSys\UserManagement\Entity\Listeners
 */
class AccessListener extends AbstractListener
{
    /**
     * AccessListener constructor.
     * @param AccessManager $manager
     */
    public function __construct( AccessManager $manager )
    {
        $this->setManager( $manager );
    }

    /**
     * @param Access    $access
     * @param EventArgs $args
     */
    public function prePersist( Access $access, EventArgs $args )
    {
        $this->getManager()->update( $access );
    }
}