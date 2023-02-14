<?php

/**
 * CoreSystems (c) 2023
 * Author: Josh McCreight<jmccreight@shaw.ca>
 */

declare(strict_types=1);

namespace CoreSys\UserManagement\DataFixtures;

use CoreSys\UserManagement\Repository\UserRepository;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use RuntimeException;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\HttpKernel\KernelInterface;

class UserFixtures extends Fixture implements DependentFixtureInterface
{
    use ContainerAwareTrait;

    public function __construct(
        protected readonly KernelInterface $kernel,
        protected readonly UserRepository $repo
    ) {
        $this->setContainer($kernel->getContainer());
    }

    public function load(ObjectManager $manager): void
    {
        $userFixtures = $this->container->getParameter('coresys_user_management.fixtures.users');

        foreach ($userFixtures ?? [] as $userFixture) {
            $this->createUser(
                $userFixture['email'],
                $userFixture['password'],
                ['roles' => $userFixture['roles'] ?? []]
            );
        }

        $manager->flush();
    }

    protected function userExists(string $email): bool
    {
        return !empty($this->repo->find($email));
    }

    protected function createUser(string $email, string $password, array $attributes = []): self
    {
        if ($this->userExists($email)) {
            throw new RuntimeException(sprintf('User `%s` already exists.', $email));
        }

        $user = $this->repo->new($email, $password, $attributes['roles'] ??= []);
        foreach ($attributes['roles'] as $key => $value) {
            if (!in_array($key, ['roles']) && method_exists($user, $method = 'set' . ucfirst($key))) {
                $user->$method($value);
            }
        }

        $this->repo->save($user, false);

        return $this;
    }

    public function getDependencies()
    {
        return [
            RoleFixtures::class,
        ];
    }
}
