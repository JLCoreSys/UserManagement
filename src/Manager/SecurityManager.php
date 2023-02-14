<?php
/**
 * CoreSystems (c) 2023
 * Author: Josh McCreight<jmccreight@shaw.ca>
 */

 declare( strict_types = 1 );

namespace CoreSys\UserManagement\Manager;

use CoreSys\UserManagement\Entity\Access;
use CoreSys\UserManagement\Entity\Role;
use CoreSys\UserManagement\Entity\User;
use CoreSys\UserManagement\Form\AccessType;
use CoreSys\UserManagement\Form\RoleType;
use CoreSys\UserManagement\Form\UserType;
use CoreSys\UserManagement\Form\UserUpdateType;
use CoreSys\UserManagement\Repository\AccessRepository;
use CoreSys\UserManagement\Repository\RoleRepository;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Yaml\Yaml;

class SecurityManager
{
    use ContainerAwareTrait;

    public const ROLE_FILE = 'coresys_roles.yaml';
    public const ACCESS_FILE = 'coresys_access.yaml';

    protected string $configurationFolder;
    protected string $env;

    public function __construct(
        KernelInterface $kernel,
        protected readonly FormFactoryInterface $formFactory,
        protected readonly RoleRepository $roleRepository,
        protected readonly AccessRepository $accessRepository
    ) {
        $this->configurationFolder = implode(DIRECTORY_SEPARATOR, [$kernel->getProjectDir(), 'config', 'packages']);
        $this->env = $kernel->getEnvironment() ?? 'dev';
        $this->setContainer($kernel->getContainer());
    }

    protected function dumpSecurityConfiguration(
        string $filename,
        array $data
    ): void {
        $message = "# This file is auto-generated. Any manual changes will be overwritten\n";
        $fs = new Filesystem();
        $fs->dumpFile($filename, $message . Yaml::dump($data, 4));
    }

    public function dumpRoles(): self
    {
        $roleStructure = $this->roleRepository->getRoleDataStructure();
        $filename = $this->configurationFolder . DIRECTORY_SEPARATOR . self::ROLE_FILE;
        $roleData = $this->getExistingSecurityConfigurationData($filename);
        $parameterName = 'coresys.security.role_hierarchy';

        if ($this->env === 'prod') {
            $roleData['parameters'][$parameterName] = $roleStructure;
        } else {
            $roleData['when@' . $this->env]['parameters'][$parameterName] = $roleStructure;
        }

        $this->dumpSecurityConfiguration($filename, $roleData);

        return $this;
    }

    public function dumpAccess(): self
    {
        $accessStructure = $this->accessRepository->getAccessDataStructure();
        $filename = $this->configurationFolder . DIRECTORY_SEPARATOR . self::ACCESS_FILE;
        $accessData = $this->getExistingSecurityConfigurationData($filename);
        $parameterName = 'coresys.security.access_control';

        if ($this->env === 'prod') {
            $accessData['parameters'][$parameterName] = $accessStructure;
        } else {
            $accessData['when@' . $this->env]['parameters'][$parameterName] = $accessStructure;
        }

        $this->dumpSecurityConfiguration($filename, $accessData);

        return $this;
    }

    protected function getExistingSecurityConfigurationData(string $filename): array
    {
        $configurationData = is_file($filename) ? Yaml::parse(file_get_contents($filename)) : [];
        $configurationData['parameters'] ??= [];

        foreach (['dev', 'test'] as $which) {
            if (!isset($configurationData[$whenKey = 'when@' . $which])) {
                $configurationData[$whenKey] = ['parameters' => []];
            } elseif (!isset($configurationData[$whenKey]['parameters'])) {
                $configurationData[$whenKey]['parameters'] = [];
            }
        }

        return $configurationData;
    }


    public function getUserForm(?User $user = null, array $options = []): FormInterface
    {
        $isNew = empty($user) || empty($user->getId());
        return $this->formFactory->create(
            !$isNew ? UserUpdateType::class : UserType::class,
            $user ?? new User(),
            $options
        );
    }

    public function getRoleForm(?Role $role = null, array $options = []): FormInterface
    {
        return $this->formFactory->create(
            RoleType::class,
            $role ?? new Role(),
            $options
        );
    }

    public function getAccessForm(?Access $access = null, array $options = []): FormInterface
    {
        return $this->formFactory->create(
            AccessType::class,
            $access ?? new Access(),
            $options
        );
    }
}
