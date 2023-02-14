<?php
/**
 * CoreSystems (c) 2023
 * Author: Josh McCreight<jmccreight@shaw.ca>
 */

 declare( strict_types = 1 );

namespace CoreSys\UserManagement\Repository;

use CoreSys\UserManagement\Entity\Access;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Access>
 *
 * @method Access|null find($id, $lockMode = null, $lockVersion = null)
 * @method Access|null findOneBy(array $criteria, array $orderBy = null)
 * @method Access[]    findAll()
 * @method Access[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AccessRepository extends AbstractRepository
{
    public function __construct(
        ManagerRegistry $registry,
        protected readonly RoleRepository $roleRepository
    ) {
        parent::__construct($registry, Access::class);
    }

    public function create(...$params): Access
    {
        $access = new Access();
        if (is_array($params)) {
            foreach ($params as $key => $val) {
                switch($key) {
                    case 'path':
                        $access->setPath($val);
                        break;
                    case 'enabled':
                    case 'public':
                    case 'mandatory':
                        $method = 'set' . ucfirst($key);
                        if (method_exists($access, $method)) {
                            $access->setMandatory(!!$val);
                        }
                        break;
                }
            }
        }

        if (isset($params['roleNames']) && is_array($params['roleNames'])) {
            foreach ($params['roleNames'] as $roleName) {
                if ($roleName === 'PUBLIC_ACCESS') {
                    $access->setPublic(true);
                    continue;
                }
    
                $role = $this->roleRepository->findOneBy(['roleName' => $roleName]);
                if ($role) {
                    $access->addRole($role);
                }
            }
        }

        return $access;
    }

    public function getAccessDataStructure(): array
    {
        $accessControl = [];

        foreach ($this->findAll() as $access) {
            if ($access->isEnabled()) {
                $accessControl[] = $access->output();
            }
        }

        return $accessControl;
    }
}
