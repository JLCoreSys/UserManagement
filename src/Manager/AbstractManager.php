<?php
/**
 * CoreSystems (c) 2020
 * Author: Josh McCreight<jmccreight@shaw.ca>
 */

declare( strict_types = 1 );

namespace CoreSys\UserManagement\Manager;

use CoreSys\UserManagement\Entity\EntityInterface;

/**
 * Class AbstractManager
 * @package CoreSys\UserManagement\Manager
 */
abstract class AbstractManager
{
    abstract public function update( EntityInterface &$entity );
    abstract public function remove( EntityInterface &$entity );
}