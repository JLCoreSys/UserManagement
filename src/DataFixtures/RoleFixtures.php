<?php

/**
 * CoreSystems (c) 2023
 * Author: Josh McCreight<jmccreight@shaw.ca>
 */

declare(strict_types=1);

namespace CoreSys\UserManagement\DataFixtures;

use CoreSys\UserManagement\Repository\RoleRepository;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\HttpKernel\KernelInterface;

class RoleFixtures extends Fixture
{
    use ContainerAwareTrait;

    public function __construct(
        protected readonly KernelInterface $kernel,
        protected readonly RoleRepository $repo
    ) {
        $this->setContainer($kernel->getContainer());
    }

    public function load(ObjectManager $manager): void
    {
        $roles = [];
        $roleFixtures = $this->container->getParameter('coresys_user_management.fixtures.roles');
        foreach ($roleFixtures as $roleFixture) {
            $inherits = [];
            foreach ($roleFixture['inherits'] ?? [] as $inheritName) {
                if ($roles[$inheritName] ?? null) {
                    $inherits[] = $roles[$inheritName];
                }
            }

            $role = $this->repo->new(
                $roleFixture['name'],
                $inherits,
                $roleFixture['color'] ?? null,
                (bool)($roleFixture['madatory'] ?? false)
            )
                ->setEnabled(true)
                ->setSwitch($roleFixture['switch'] ?? false);

            $this->repo->save($role, false);
            $roles[$role->getName()] = $role;
        }

        $manager->flush();
    }
}
