<?php
/**
 * CoreSystems (c) 2023
 * Author: Josh McCreight<jmccreight@shaw.ca>
 */

 declare( strict_types = 1 );

namespace CoreSys\UserManagement\Repository;

use CoreSys\UserManagement\Entity\Role;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use RuntimeException;

/**
 * @extends ServiceEntityRepository<Role>
 *
 * @method Role|null find($id, $lockMode = null, $lockVersion = null)
 * @method Role|null findOneBy(array $criteria, array $orderBy = null)
 * @method Role[]    findAll()
 * @method Role[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RoleRepository extends AbstractRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Role::class);
    }

    /** @return mixed */
    public function find($id, $lockMode = null, $lockVersion = null)
    {
        if (is_numeric($id) || is_array($id)) {
            return parent::find($id, $lockMode, $lockVersion);
        }

        return $this->findOneBy(['roleName' => (string)$id]);
    }

    public function create(...$params): Role
    {
        $role = new Role();

        if (is_array($params)) {
            foreach ($params as $key => $val) {
                switch ($key) {
                    case 'name':
                    case 'color':
                        $method = 'set' . ucfirst($key);
                        if (method_exists($role, $method)) {
                            $role->$method((string)$val);
                        }
                        break;
                    case 'mandatory':
                    case 'switch':
                        $method = 'set' . ucfirst($key);
                        if (method_exists($role, $method)) {
                            $role->$method((bool)$val);
                        }
                        break;
                }
            }
        }

        foreach ($params['inherits'] ??= [] as $inheritName) {
            if ($inheritName instanceof Role) {
                $role->addInherit($inheritName);
            } else {
                $inheritRole = $this->findOneBy(['name' => $inheritName]);
                if ($inheritRole) {
                    $role->addInherit($inheritRole);
                }
            }
        }

        return $role;
    }

    /**
     * Remove a role
     *
     * @param Role $entity
     * @param boolean $flush
     * @return void
     */
    public function remove(mixed $entity, bool $flush = false): void
    {
        if ($entity->isMandatory()) {
            throw new RuntimeException('Cannot remove mandatory role.');
        }

        parent::remove($entity, $flush);
    }

    public function getRoleDataStructure(): array
    {
        $hier = [];
        foreach ($this->findAll() as $role) {
            if ($role->isEnabled()) {
                $roleNames = array_filter(
                    $role->getRoleNames(),
                    function ($rn) use ($role) {
                        return $rn !== $role->getRoleName();
                    }
                );
                $hier[$role->getRoleName()] = array_values($roleNames);
            }
        }

        return $hier;
    }
}
