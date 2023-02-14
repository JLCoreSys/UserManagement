<?php

namespace CoreSys\UserManagement\Tests\Repository;

use CoreSys\UserManagement\Entity\Role;
use CoreSys\UserManagement\Manager\SecurityManager;
use CoreSys\UserManagement\Repository\RoleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Yaml\Yaml;

class RoleRepositoryTest extends KernelTestCase
{
    protected const ROLENAME_TESTS = [
        'user' => 'ROLE_USER',
        'user2' => 'ROLE_USER2',
        'user-2' => 'ROLE_USER_2',
        'user--2' => 'ROLE_USER_2',
        'user_2' => 'ROLE_USER_2',
        'user__2' => 'ROLE_USER_2',
        'user_-2' => 'ROLE_USER_2',
        'user_- 2' => 'ROLE_USER_2',
        'user   2' => 'ROLE_USER_2',
        'user ^  2** ' => 'ROLE_USER_2',
    ];
    protected EntityManagerInterface $entityManager;
    protected RoleRepository $roleRepository;
    protected string $roleFilename;

    protected function setUp(): void
    {
        $kernel = self::bootKernel();
        $this->assertSame('test', $kernel->getEnvironment());
        $this->entityManager = $kernel->getContainer()
            ->get('doctrine')
            ->getManager();

        $container = static::getContainer();
        $this->roleRepository = $container->get(RoleRepository::class);
        $this->roleFilename = implode(
            DIRECTORY_SEPARATOR,
            [
                $kernel->getProjectDir(),
                'config',
                'packages',
                SecurityManager::ROLE_FILE
            ]
        );
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->entityManager->close();
    }

    public function testRoleNames(): void
    {
        foreach (self::ROLENAME_TESTS as $name => $expected) {
            $roleName = Role::nameToRoleName($name);
            $this->assertEquals($expected, $roleName, sprintf('`%s` does not match expected `%s`', $roleName, $expected));
        }
    }

    public function testFixtureLoad(): void
    {
        $this->assertCount(5, $this->roleRepository->findAll(), 'Unexpected roles count.');
    }

    public function testCreateRole(): void
    {
        $role = $this->roleRepository->new('ROLE_TEST', ['User']);
        $this->roleRepository->save($role, true);

        $this->assertCount(6, $this->roleRepository->findAll(), 'Unexpected roles count.');
        $role = $this->roleRepository->find($role->getId());
        $this->assertNotEmpty($role, 'Role was not returned from the database.');
        $this->assertCount(1, $role->getInherits(), 'Role inherits did not set.');

        $user = $this->roleRepository->find('ROLE_USER');
        $this->assertNotEmpty($user, 'User role was empty.');
        $this->assertTrue($user->getInheritedBy()->contains($role), 'User is not inherited by ROLE_TEST.');
    }

    public function testRoleConfigurationPostCreate(): void
    {
        $config = $this->getRoleHierarchyFromYaml();
        $this->assertCount(6, $config, 'Role count mismatch.');
        $this->assertArrayHasKey('ROLE_TEST', $config, 'Role config does not contain `ROLE_TEST`');
        $this->assertCount(1, $config['ROLE_TEST'] ?? [], 'Role `ROLE_TEST` did not write properly.');
    }

    public function testUpdateRole(): void
    {
        $test = $this->roleRepository->find('ROLE_TEST');
        $this->assertNotEmpty($test, '`ROLE_TEST` not returned from the databse.');

        $member = $this->roleRepository->find('ROLE_MEMBER');
        $this->assertNotEmpty($member, '`ROLE_MEMBER` not returned from the database.');

        /** @var Role $test */
        $test->addInherit($member);
        $this->roleRepository->save($test, true);

        $test = $this->roleRepository->find('ROLE_TEST');
        $this->assertNotEmpty($test, '`ROLE_TEST` not returned from the databse.');

        $member = $this->roleRepository->find('ROLE_MEMBER');
        $this->assertNotEmpty($member, '`ROLE_MEMBER` not returned from the database.');

        $this->assertTrue($test->getInherits()->contains($member), '`ROLE_TEST` does not inherit `ROLE_MEMBER`.');
        $this->assertTrue($member->getInheritedBy()->contains($test), '`ROLE_MEMBER` is not inherited by `ROLE_TEST`');
    }

    public function testRoleConfigurationPostUpdate(): void
    {
        $config = $this->getRoleHierarchyFromYaml();
        $this->assertCount(6, $config, 'Role count mismatch.');
        $this->assertArrayHasKey('ROLE_TEST', $config, 'Role config does not contain `ROLE_TEST`');
        $this->assertCount(2, $config['ROLE_TEST'] ?? [], 'Role `ROLE_TEST` did not write properly.');
    }

    protected function getRoleHierarchyFromYaml(): array
    {
        $this->assertTrue(file_exists($this->roleFilename), 'Roles YAML file not found.');

        $yaml = Yaml::parseFile($this->roleFilename);
        $this->assertArrayHasKey('when@test', $yaml, 'Role config does not contain `when@test`');
        $testYaml = $yaml['when@test'];
        $this->assertArrayHasKey('parameters', $testYaml, 'Role config when@test does not contain parameters');

        return $testYaml['parameters']['coresys.security.role_hierarchy'] ?? [];
    }

    public function testRemoveRole(): void
    {
        $role = $this->roleRepository->find('ROLE_TEST');
        $this->assertNotEmpty($role, 'ROLE_TEST is empty.');
        $this->roleRepository->remove($role, true);

        $role = $this->roleRepository->find('ROLE_TEST');
        $this->assertEmpty($role, '`ROLE_TEST` should be empty.');
    }

    public function testRoleConfigurationPostRemove(): void
    {
        $config = $this->getRoleHierarchyFromYaml();
        $this->assertArrayNotHasKey('ROLE_TEST', $config, '`ROLE_TEST` should have been removed.');
    }
}
