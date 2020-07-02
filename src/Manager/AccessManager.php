<?php
/**
 * CoreSystems (c) 2020
 * Author: Josh McCreight<jmccreight@shaw.ca>
 */

declare( strict_types = 1 );

namespace CoreSys\UserManagement\Manager;

use CoreSys\UserManagement\Entity\EntityInterface;

/**
 * Class AccessManager
 * @package CoreSys\UserManagement\Manager
 */
class AccessManager extends AbstractManager
{
    /**
     * @param EntityInterface $entity
     */
    public function remove( EntityInterface &$entity )
    {
        // TODO: Implement remove() method.
    }

    /**
     * @param EntityInterface $entity The Access entity
     */
    public function update( EntityInterface &$entity )
    {
        // TODO: Implement update() method.
    }
}