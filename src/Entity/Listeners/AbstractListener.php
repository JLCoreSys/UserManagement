<?php
/**
 * CoreSystems (c) 2020
 * Author: Josh McCreight<jmccreight@shaw.ca>
 */

declare( strict_types = 1 );

namespace CoreSys\UserManagement\Entity\Listeners;

use CoreSys\UserManagement\Manager\AbstractManager;

/**
 * Class AbstractListener
 * @package CoreSys\UserManagement\Entity\Listeners
 */
abstract class AbstractListener
{
    /**
     * @var AbstractManager
     */
    protected $manager;

    /**
     * Get Manager
     * @return AbstractManager
     */
    public function getManager(): AbstractManager
    {
        return $this->manager;
    }

    /**
     * Set Manager
     * @param AbstractManager $manager
     * @return AbstractListener
     */
    public function setManager( AbstractManager $manager ): AbstractListener
    {
        $this->manager = $manager;

        return $this;
    }
}