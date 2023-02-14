<?php

namespace CoreSys\UserManagement\Tests\Repository;

use CoreSys\UserManagement\Entity\Access;
use CoreSys\UserManagement\Entity\Role;
use CoreSys\UserManagement\Manager\SecurityManager;
use CoreSys\UserManagement\Repository\AccessRepository;
use CoreSys\UserManagement\Repository\RoleRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class AccessRepositoryTest extends KernelTestCase
{
    const ACCESS_DEFAULTS = [
        ['path' => '^/', 'roles' => ['PUBLIC_ACCESS']],
        ['path' => '^/(login|logout)', 'roles' => ['PUBLIC_ACCESS']],
        ['path' => '^/members', 'roles' => ['ROLE_MEMBER']],
        ['path' => '^/admin', 'roles' => ['ROLE_ADMIN']],
        ['path' => '^/admin/(login|logout)', 'roles' => ['PUBLIC_ACCESS']]
    ];

    const TEST_ACCESS_DEFAULTS = [
        [
            'path' => '^/test/access',
            'enabled' => true,
            'methods' => Access::METHODS,
            'public' => false,
            'roles' => [],
            'mandatory' => true,
        ], [
            'enabled' => false,
            'methods' => ['GET', 'POST', 'PUT', 'DELETE'],
            'public' => true,
            'roles' => ['PUBLIC_ACCESS']
        ], [
            'enabled' => true,
            'methods' => Access::METHODS,
            'public' => false,
            'roles' => ['ROLE_ADMIN'],
            'mandatory' => false
        ]
    ];

    protected EntityManagerInterface $entityManager;
    protected AccessRepository $accessRepository;
    protected RoleRepository $roleRepository;
    protected string $accessFilename;

    protected function getTestData(int $level = 0): array
    {
        if ($level === 0) {
            return self::TEST_ACCESS_DEFAULTS[$level];
        }

        return array_merge(
            $this->getTestData($level - 1),
            self::TEST_ACCESS_DEFAULTS[$level]
        );
    }

    protected function setUp(): void
    {
        $kernel = self::bootKernel();
        $this->assertSame('test', $kernel->getEnvironment());
        $this->entityManager = $kernel->getContainer()
            ->get('doctrine')
            ->getManager();

        $container = static::getContainer();
        $this->accessRepository = $container->get(AccessRepository::class);
        $this->roleRepository = $container->get(RoleRepository::class);
        $this->accessFilename = implode(
            DIRECTORY_SEPARATOR,
            [
                $kernel->getProjectDir(),
                'config',
                'packages',
                SecurityManager::ACCESS_FILE
            ]
        );
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->entityManager->close();
    }

    public function testFixtureLoad(): void
    {
        $this->assertCount(
            count(self::ACCESS_DEFAULTS),
            $this->accessRepository->findBy(['enabled' => true]),
            'Unexpected accesss count.'
        );
    }

    public function testAccessDefaults(): void
    {
        $accesses = $this->accessRepository->findAll();
        foreach ($accesses as &$access) {
            if ($access->isEnabled()) {
                if (false === ($defaultAccess = array_filter(self::ACCESS_DEFAULTS, function ($item) use ($access) {
                    return $item['path'] === $access->getPath();
                }))) {
                    $this->assertTrue(count($defaultAccess) === 1, sprintf('No matching access path found for `%s`', $access->getPath()));
                }

                foreach (($defaultAccess = $defaultAccess[array_key_first($defaultAccess)])['roles'] ?? [] as $roleName) {
                    /** @var Access $defaultAccess */
                    $this->assertTrue($access->hasRoleName($roleName), sprintf('Access does not contain the role `%s`', $roleName));
                }

                $assertPublicMethod = 'assert' . (in_array('PUBLIC_ACCESS', $defaultAccess['roles'] ?? []) ? 'True' : 'False');
                $this->$assertPublicMethod($access->isPublic(), sprintf('Public mismatch for `%s`', $access->getPath()));
            } else {
                $this->assertEquals('^/admin/super', $access->getPath(), 'Only InActive access does not match.');
                $this->assertFalse($access->isPublic(), 'admin/super should not be public.');
            }
        }
    }

    public function testAccessCreate(): void
    {
        $createData = $this->getTestData(0);

        $access = $this->accessRepository->new(
            $createData['path'],
            $createData['roles'] ?? [],
            $createData['enabled'] ?? true,
            $createData['public'] ?? false,
            $createData['mandatory'] ?? false
        );
        $this->accessRepository->save($access, true);

        $access = $this->accessRepository->findOneBy(['path' => $createData['path']]);
        $this->assertNotEmpty($access, 'Access not found.');
    }

    public function testAccessUpdate(): void
    {
        $access = $this->accessRepository->findOneBy(['path' => self::TEST_ACCESS_DEFAULTS[0]['path']]);
        $this->assertNotEmpty($access, 'Access not found.');

        for ($i = 1; $i <= 2; $i++) {
            foreach (self::TEST_ACCESS_DEFAULTS[2] as $key => $value) {
                if ($key === 'roles' && is_array($value)) {
                    $value = $this->getRolesByName($value);
                }
                $this->assertTrue(method_exists($access, $setter = 'set' . ucfirst($key)), sprintf('Method %s not found', $setter));
                $access->$setter($value);
            }

            $this->accessRepository->save($access, true);
            foreach (self::TEST_ACCESS_DEFAULTS[2] as $key => $value) {
                if ($key === 'roles') {
                    $value = $this->getRolesByName($value);
                    $this->assertEquals(count($access->getRoles()), count($value), 'Role count doesnt match.');

                    foreach ($value as $role) {
                        /** @var Role $role */
                        $this->assertTrue($role instanceof Role, 'Role is not an instance of Role.');
                        $this->assertTrue($access->getRoles()->contains($role), sprintf(
                            'Access does not contain the role `%s`',
                            $role->getRoleName()
                        ));
                    }

                    continue;
                }

                $getter = 'get' . ucfirst($key);
                if (!method_exists($access, $getter)) {
                    $getter = 'is' . ucfirst($key);
                }
                $this->assertTrue(method_exists($access, $getter), sprintf('Method %s not found', $getter));
                $this->assertEquals($access->$getter(), $value);
            }
        }
    }

    protected function getRolesByName(array $roleNames = []): Collection
    {
        $roles = new ArrayCollection();
        foreach ($roleNames as $roleName) {
            if (!empty($role = $this->roleRepository->findOneBy(['roleName' => $roleName]))) {
                $roles->add($role);
            }
        }

        return $roles;
    }

    public function testAccessDelete(): void
    {
        $access = $this->accessRepository->findOneBy(['path' => self::TEST_ACCESS_DEFAULTS[0]['path']]);
        $this->assertNotEmpty($access, 'Access not found.');

        $this->accessRepository->remove($access, true);

        $access = $this->accessRepository->findOneBy(['path' => self::TEST_ACCESS_DEFAULTS[0]['path']]);
        $this->assertEmpty($access, 'Access was not removed.');
    }
}
