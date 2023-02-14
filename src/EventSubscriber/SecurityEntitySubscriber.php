<?php
/**
 * CoreSystems (c) 2023
 * Author: Josh McCreight<jmccreight@shaw.ca>
 */

 declare(strict_types=1);

namespace CoreSys\UserManagement\EventSubscriber;

use CoreSys\UserManagement\Entity\Access;
use CoreSys\UserManagement\Entity\Role;
use CoreSys\UserManagement\Manager\SecurityManager;
use Doctrine\Bundle\DoctrineBundle\EventSubscriber\EventSubscriberInterface;
use Doctrine\ORM\Event\PostFlushEventArgs;
use Doctrine\ORM\Event\PrePersistEventArgs;
use Doctrine\ORM\Event\PreRemoveEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Events;

class SecurityEntitySubscriber implements EventSubscriberInterface
{
    protected static ?Role $role = null;
    protected static ?Access $access = null;

    public function __construct(protected readonly SecurityManager $securityManager)
    {
        // stub
    }

    public function getSubscribedEvents(): array
    {
        return [
            Events::prePersist,
            Events::preUpdate,
            Events::postFlush,
            Events::preRemove,
        ];
    }

    public function supports(string $entityClass): bool
    {
        return $entityClass === Role::class || $entityClass === Access::class;
    }

    public function prePersist(PrePersistEventArgs $args): void
    {
        if (!$this->supports(get_class($entity = $args->getObject()))) {
            return;
        }

        if (empty($entity->getId())) {
            $which = $entity::class === Role::class ? 'role' : 'access';
            self::$$which = $entity;
        }
    }

    public function preUpdate(PreUpdateEventArgs $args): void
    {
        if (!$this->supports(get_class($entity = $args->getObject()))) {
            return;
        }

        $force = false;

        if ($entity::class === Role::class) {
            // Role
            // $updatableChangeKeys = array_keys($args->getEntityChangeSet(
                // ['name', 'switch', 'inherits', 'enabled', 'inheritedBy']
            // ));
            $updatableChangeKeys = array_keys($args->getEntityChangeSet());

            /** @var Role $entity */
            $inheritedBy = $entity->getInheritedBy();
            if (method_exists($inheritedBy, $method = 'isDirty')) {
                if ($inheritedBy->$method()) {
                    $force = true;
                }
            }

            $inherits = $entity->getInherits();
            if (method_exists($inherits, $method = 'isDirty')) {
                if ($inherits->$method()) {
                    $force = true;
                }
            }
        } else {
            // Access
            $updatableChangeKeys = array_keys($args->getEntityChangeSet());

            /** @var Access $entity */
            $roles = $entity->getRoles();
            if (method_exists($roles, $method = 'isDirty')) {
                if ($roles->$method()) {
                    $force = true;
                }
            }
        }

        if (count($updatableChangeKeys) > 0 || $force) {
            $which = $entity::class === Role::class ? 'role' : 'access';
            self::$$which = $entity;
        }
    }

    public function preRemove(PreRemoveEventArgs $args): void
    {
        if (!$this->supports(get_class($entity = $args->getObject()))) {
            return;
        }

        $which = $entity::class === Role::class ? 'role' : 'access';
        self::$$which = $entity;
    }

    public function postFlush(PostFlushEventArgs $args): void
    {
        if (self::$role && self::$role::class === Role::class) {
            $this->securityManager->dumpRoles();
            self::$role = null;
        }

        if (self::$access && self::$access::class === Access::class) {
            $this->securityManager->dumpAccess();
            self::$access = null;
        }
    }
}
