<?php
/**
 * CoreSystems (c) 2020
 * Author: Josh McCreight<jmccreight@shaw.ca>
 */

declare( strict_types = 1 );

namespace CoreSys\UserManagement\Manager;

use CoreSys\UserManagement\Entity\EntityInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\KernelInterface;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Class AbstractManager
 * @package CoreSys\UserManagement\Manager
 */
abstract class AbstractManager
{
    /**
     * @var ContainerInterface|null
     */
    protected $container;

    /**
     * @var KernelInterface|null
     */
    protected $kernel;
    
    /**
     * @var EntityManagerInterface
     */
    protected $entityManager;

    /**
     * Get Kernel
     * @return KernelInterface|null
     */
    public function getKernel(): ?KernelInterface
    {
        return $this->kernel;
    }

    /**
     * @return ContainerInterface|null
     */
    protected function getContainer(): ?ContainerInterface
    {
        return $this->container;
    }

    /**
     * @return EntityManagerInterface|null
     */
    protected function getEntityManager(): ?EntityManagerInterface
    {
        return $this->entityManager;
    }

    /**
     * Set EntityManager
     * @param EntityManagerInterface $entityManager
     * @return AbstractManager
     */
    public function setEntityManager( EntityManagerInterface $entityManager ): AbstractManager
    {
        $this->entityManager = $entityManager;

        return $this;
    }
}