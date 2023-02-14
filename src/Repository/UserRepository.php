<?php
/**
 * CoreSystems (c) 2023
 * Author: Josh McCreight<jmccreight@shaw.ca>
 */

 declare( strict_types = 1 );

namespace CoreSys\UserManagement\Repository;

use CoreSys\UserManagement\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use InvalidArgumentException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;

/**
 * @extends ServiceEntityRepository<User>
 *
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends AbstractRepository implements PasswordUpgraderInterface
{
    public function __construct(
        ManagerRegistry $registry,
        protected readonly UserPasswordHasherInterface $passwordHasher,
        protected readonly RoleRepository $roleRepository,
    ) {
        parent::__construct($registry, User::class);
    }

    /** @return mixed */
    public function find($id, $lockMode = null, $lockVersion = null)
    {
        if (is_numeric($id) || is_array($id)) {
            return parent::find($id, $lockMode, $lockVersion);
        }

        return $this->findOneBy(['email' => (string)$id]);
    }

    public function create(...$params): User
    {
        [$email, $password, $roles] = $params;

        if (empty($email) || empty($password) || empty($roles)) {
            throw new InvalidArgumentException('To create a user, email, password and roles are required.');
        }

        $user = (new User())
            ->setEmail((string) $email)
            ->setPlainPassword((string) $password)
            ->setRoles((array) $roles);

        return $user;
    }

    /**
     * Undocumented function
     *
     * @param User $user
     * @param boolean $flush
     * @return mixed
     */
    public function save(mixed $user, bool $flush = false): void
    {
        if (!$user instanceof User) {
            throw new InvalidArgumentException('Entity must be an instance of User.');
        }

        if (!empty($plainPassword = $user->getPlainPassword())) {
            $this->setUserPassword($user, $plainPassword);
        }

        $this->syncRolesToSystemRoles($user)
            ->pruneRoles($user)
            ->syncSystemRolesToRoles($user)
            ->pruneRoles($user);

        $this->getEntityManager()->persist($user);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function updatePassword(User $user, string $plainPassword, bool $flush = false): void
    {
        $this->setUserPassword($user, $plainPassword);
        $this->save($user, $flush);
    }

    protected function setUserPassword(User $user, string $plainPassword): void
    {
        $user->setPlainPassword(null)
            ->setPassword(
                $this->passwordHasher
                    ->hashPassword($user, $plainPassword)
            );
    }

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     */
    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', get_class($user)));
        }

        $user->setPassword($newHashedPassword);

        $this->save($user, true);
    }

    public function syncRolesToSystemRoles(User $user): self
    {
        foreach ($user->getRoles() as $roleName) {
            $systemRole = $this->roleRepository->findOneBy(['roleName' => $roleName]);
            if ($systemRole && method_exists($user, 'addSystemRole')) {
                $user->addSystemRole($systemRole);
            }
        }

        return $this;
    }

    public function syncSystemRolesToRoles(User $user): self
    {
        foreach ($user->getSystemRoles() as $systemRole) {
            $user->addRole($systemRole->getRoleName());
        }

        return $this;
    }

    public function pruneRoles(User $user): self
    {
        $stringRoles = $user->getRoles();
        $systemRoles = [];
        foreach ($user->getSystemRoles() as $systemRole) {
            $systemRoles[$systemRole->getRoleName()] = $systemRole;
        }

        $stringDiff = array_diff($stringRoles, $systemRoleKeys = array_keys($systemRoles));

        foreach ($stringDiff as $diff) {
            if ($diff !== 'ROLE_USER') {
                $user->removeRole($diff);
            }
        }

        $systemRolesDiff = array_diff($systemRoleKeys, $stringRoles);
        foreach ($systemRolesDiff as $diffKey) {
            $diff = $systemRoles[$diffKey];
            if ($diff->getRoleName() !== 'ROLE_USER') {
                $user->removeSystemRole($diff);
            }
        }

        return $this;
    }
}
