<?php

namespace CoreSys\UserManagement\Tests\Repository;

use CoreSys\UserManagement\Entity\User;
use CoreSys\UserManagement\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class UserRepositoryTest extends KernelTestCase
{
    protected const EMAIL = 'test.user@domain.com';

    protected EntityManagerInterface $entityManager;
    protected UserRepository $userRepository;

    protected function setUp(): void
    {
        $kernel = self::bootKernel();
        $this->assertSame('test', $kernel->getEnvironment());
        $this->entityManager = $kernel->getContainer()
            ->get('doctrine')
            ->getManager();

        $container = static::getContainer();
        $this->userRepository = $container->get(UserRepository::class);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->entityManager->close();
    }

    protected function removeUser(?bool $assert = false): void
    {
        $user = $this->userRepository->find(self::EMAIL);
        if ($assert) {
            $this->assertNotEmpty($user, 'User is empty');
        }

        if (!empty($user)) {
            $this->userRepository->remove($user, true);

            if ($assert) {
                $user = $this->userRepository->find(self::EMAIL);
                $this->assertEmpty($user, 'User was not removed.');
            }
        }
    }

    public function testCreateUser(): void
    {
        $this->removeUser();

        $user = $this->userRepository->new(self::EMAIL, 'password', []);
        $this->assertTrue($user instanceof User, 'User was not a User.');
        $this->userRepository->save($user, true);

        // fetch a new user
        $user = $this->userRepository->find(self::EMAIL);
        $this->assertTrue($user instanceof User, 'User was not returned.');
        $this->assertTrue($user->hasRole('ROLE_USER'), 'User does not have the role ROLE_USER');
    }

    public function testUpdateUserRoles(): void
    {
        /** @var User $user */
        $user = $this->userRepository->find(self::EMAIL);
        $this->assertTrue($user instanceof User, 'User was not returned.');
        $user->addRole('ROLE_ADMIN');
        $this->assertTrue($user->hasRole('ROLE_ADMIN'), 'User ROLE_ADMIN was not added.');
        $this->assertFalse($user->hasSystemRoleName('ROLE_ADMIN'), 'User should not have ROLE_ADMIN before saving.');
        $this->userRepository->save($user, true);

        /** @var User $user */
        $user = $this->userRepository->find(self::EMAIL);
        $this->assertTrue($user instanceof User, 'User was not returned.');
        $this->assertTrue($user->hasRole('ROLE_ADMIN'), 'User role was not saved.');
        $this->assertTrue($user->hasSystemRoleName('ROLE_ADMIN', 'User role should have System role ROLE_ADMIN.'));
        $this->assertTrue($user->hasSystemRoleName('ROLE_USER', 'User role should have System role ROLE_USER.'));
        $user->removeRole('ROLE_ADMIN');
        $this->userRepository->save($user, true);

        /** @var User $user */
        $user = $this->userRepository->find(self::EMAIL);
        $this->assertFalse($user->hasSystemRoleName('ROLE_ADMIN'), 'User should not have System ROLE_ADMIN.');
        $this->assertTrue($user instanceof User, 'User was not returned.');
        $this->assertFalse($user->hasRole('ROLE_ADMIN'), 'Admin role was not removed.');

        // attempt to remove ROLE_USER #1 - set an empty array
        $user->setRoles([]);
        $this->assertTrue($user->hasRole('ROLE_USER'), 'User does not have ROLE_USER #1');
        $this->assertContains('ROLE_USER', $user->getRoles(), 'User does not have ROLE_USER #1.1');

        // attempt to remove ROLE_USER #2 - remove the role
        $user->removeRole('ROLE_USER');
        $this->assertTrue($user->hasRole('ROLE_USER'), 'User does not have ROLE_USER #2');
        $this->assertContains('ROLE_USER', $user->getRoles(), 'User does not have ROLE_USER #2.1');
    }

    public function testUserPasswords(): void
    {
        $this->assertTrue(true, 'Not true');
    }

    public function testRemoveUser(): void
    {
        $this->removeUser(true);
    }
}
