<?php
/**
 * CoreSystems (c) 2020
 * Author: Josh McCreight<jmccreight@shaw.ca>
 */

declare( strict_types = 1 );

namespace CoreSys\UserManagement\Subscribers;

use CoreSys\UserManagement\Entity\User;
use CoreSys\UserManagement\Manager\UserManager;
use Doctrine\Bundle\DoctrineBundle\EventSubscriber\EventSubscriberInterface;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Events;

class UserEntitySubscriber implements EventSubscriberInterface
{
    /**
     * @var UserManager
     */
    protected $userManager;

    /**
     * UserEntitySubscriber constructor.
     * @param UserManager $manager
     */
    public function __construct( UserManager $manager )
    {
        $this->userManager = $manager;
    }

    public function getSubscribedEvents()
    {
        return [
            Events::prePersist,
            Events::postPersist
        ];
    }

    public function prePersist( LifecycleEventArgs $args )
    {
        $user = $args->getEntity();

        if ( $this->supports( $user ) ) {
            $this->userManager->update( $user );
        }
    }

    /**
     * @param $entity
     * @return bool
     */
    public function supports( $entity )
    {
        return $entity instanceof User;
    }

    public function postPersist( LifecycleEventArgs $args )
    {
        $user = $args->getEntity();

        if ( $this->supports( $user ) ) {
            $this->userManager->update( $user );
        }
    }
}