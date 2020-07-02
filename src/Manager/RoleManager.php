<?php
/**
 * CoreSystems (c) 2020
 * Author: Josh McCreight<jmccreight@shaw.ca>
 */

declare( strict_types = 1 );

namespace CoreSys\UserManagement\Manager;

use CoreSys\UserManagement\Entity\EntityInterface;
use CoreSys\UserManagement\Entity\Role;
use CoreSys\UserManagement\Manager\Traits\ConfigurationYaml;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Yaml\Yaml;

/**
 * Class RoleManager
 * @package CoreSys\UserManagement\Manager
 */
class RoleManager extends AbstractManager
{
    use ConfigurationYaml;

    /**
     * @var ParameterBag
     */
    protected $configuration;

    /**
     * RoleManager constructor.
     * @param KernelInterface $kernel
     * @param array           $config
     */
    public function __construct( KernelInterface $kernel, array $config = [] )
    {
        $this->configuration = $config;
        $this->setKernel( $kernel )
            ->setFile( $this->getConfigurationFile( 'roles.yaml' ) )
            ->setup();
    }

    /**
     * @return RoleManager
     */
    protected function setup(): RoleManager
    {
        $repo = NULL; // @todo
        $roles = [ 'User' => NULL, 'Member' => NULL, 'Admin' => NULL, 'Super Admin' => NULL ];
        $previous = NULL;

        foreach ( $roles as $roleName => $role ) {
            $role = $repo->findOneBy( [ 'name' => $roleName ] );
            if ( !$role instanceof Role ) {
                $role = ( new Role() )
                    ->setMandatory( TRUE )
                    ->setName( $roleName );

                if ( $previous instanceof Role ) {
                    $role->setParent( $previous );
                }

                // @todo persist and flush
            }

            $previous = $role;
        }

        return $this;
    }

    /**
     * @return array
     */
    public function getRoleNamePairs()
    {
        $pairs = [];

        foreach ( $this->getAllRoles() as &$role ) {
            $role = $role ?? new Role();
            $pairs[ $role->getId() ] = $role->getName();
        }

        return $pairs;
    }

    /**
     * Get a complete list of system roles
     *
     * @return Collection
     */
    public function getAllRoles(): Collection
    {
        // @todo return all roles from the Db
        return new ArrayCollection();
    }

    /**
     * Get Configuration
     * @return array|null
     */
    public function getConfiguration(): ?array
    {
        return $this->configuration;
    }

    /**
     * Set Configuration
     * @param array|null $configuration
     * @return RoleManager
     */
    public function setConfiguration( ?array $configuration ): RoleManager
    {
        $this->configuration = $configuration;

        return $this;
    }

    /**
     * @param bool $yaml
     * @return array|null|string
     */
    public function getDataStructure( $yaml = FALSE )
    {
        $hier = [];

        foreach ( $this->getAllRoles() as $role ) {
            $role = $role ?? new Role();
            $rhier = [];
            $parent = $role->getParent();
            if ( !empty( $parent ) ) {
                $rhier[] = $parent->getRoleName();
            } else {
                $rhier[] = 'ROLE_USER';
            }

            if ( $role->isSwitch() ) {
                $rhier[] = 'ROLE_ALLOWED_TO_SWITCH';
            }

            if ( $role->isActive() ) {
                $hier[ $role->getRoleName() ] = array_unique( $rhier );
            }
        }

        if ( count( $hier ) === 0 ) {
            return NULL;
        }

        return $yaml ? Yaml::dump( $hier, 1 ) : $hier;
    }

    /**
     * @param EntityInterface $entity
     * @return $this
     */
    public function remove( EntityInterface &$entity )
    {
        $role = $entity ?? new Role();
        foreach ( $entity->getUsers() as &$user ) {
            $user->removeSystemRole( $role );
            // @todo persist the user
        }

        // flush the db
        // @todo flush the db

        return $this;
    }

    /**
     * Update the roles
     *
     * @param EntityInterface $entity
     * @return RoleManager
     */
    public function update( EntityInterface &$entity )
    {
        $entity = $entity ?? new Role();
        $entity->setRoleName(
            "ROLE_" . strtoupper(
                str_replace( ' ', '_', trim( $entity->getName() )
                )
            )
        );

        return $this;
    }
}