<?php

/**
 * CoreSystems (c) 2023
 * Author: Josh McCreight<jmccreight@shaw.ca>
 */

declare(strict_types=1);

namespace CoreSys\UserManagement\DataFixtures;

use CoreSys\UserManagement\Repository\AccessRepository;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\HttpKernel\KernelInterface;

class AccessFixtures extends Fixture implements DependentFixtureInterface
{
    use ContainerAwareTrait;

    public function __construct(
        protected readonly KernelInterface $kernel,
        protected readonly AccessRepository $repo
    ) {
        $this->setContainer($kernel->getContainer());
    }

    public function load(ObjectManager $manager): void
    {
        $access = [];
        $fixtures = $this->container->getParameter('coresys_user_management.fixtures.access');
        foreach ($fixtures as $fixture) {
            $access = $this->repo->new(
                $fixture['path'] ?? '/',
                $fixture['roles'] ?? [],
                $fixture['enabled'] ?? true,
                $fixture['public'] ?? false,
                $fixture['mandatory'] ?? false
            );

            $this->repo->save($access, false);
        }

        $manager->flush();
    }

    public function getDependencies()
    {
        return [
            UserFixtures::class,
        ];
    }
}
