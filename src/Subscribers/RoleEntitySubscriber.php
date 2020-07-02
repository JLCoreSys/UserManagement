<?php
/**
 * CoreSystems (c) 2020
 * Author: Josh McCreight<jmccreight@shaw.ca>
 */

declare( strict_types = 1 );

namespace CoreSys\UserManagement\Subscribers;

use CoreSys\UserManagement\Entity\Role;
use CoreSys\UserManagement\Manager\RoleManager;
use Doctrine\Bundle\DoctrineBundle\EventSubscriber\EventSubscriberInterface;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PostFlushEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Events;

class RoleEntitySubscriber implements EventSubscriberInterface
{
    /**
     * @var RoleManager
     */
    protected $roleManager;

    /**
     * @var Role|null
     */
    protected $role;

    /**
     * RoleEntitySubscriber constructor.
     * @param RoleManager $manager
     */
    public function __construct( RoleManager $manager )
    {
        $this->roleManager = $manager;
    }

    public function getSubscribedEvents()
    {
        return [
            Events::prePersist,
            Events::preUpdate,
            Events::postFlush
        ];
    }

    public function prePersist( LifecycleEventArgs $args )
    {
        if ( $this->supports( $role = $args->getEntity() ) ) {
            $this->roleManager->update( $role );
            $this->role = $role;
        }
    }

    /**
     * @param $entityClass
     * @return bool
     */
    public function supports( $entityClass )
    {
        return $entityClass === Role::class || $entityClass instanceof Role;
    }

    public function preUpdate( PreUpdateEventArgs $args )
    {
        if ( $this->supports( $role = $args->getEntity() ) ) {
            $this->roleManager->update( $role );
            $this->role = $role;
        }
    }

    public function postFlush( PostFlushEventArgs $args )
    {
        if ( !empty( $this->role ) && $this->supports( $this->role ) ) {
            $this->roleManager->dumpYaml();
        }
    }
}