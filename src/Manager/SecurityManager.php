<?php
/**
 * CoreSystems (c) 2023
 * Author: Josh McCreight<jmccreight@shaw.ca>
 */

declare(strict_types=1);

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
use CoreSys\UserManagement\UserManagementBundle;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Yaml\Yaml;

class SecurityManager
{
    use ContainerAwareTrait;


    public const ROLES_PARAMETER = 'security.role_hierarchy';
    public const ACCESS_CONTROL_PARAMETER = 'security.access_control';

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
        $message = "# parameters are auto-generated. Any manual changes will be overwritten\n";
        $fs = new Filesystem();
        $fs->dumpFile($filename, $message . Yaml::dump($data, 4));
    }

    protected function buildPath(array $pieces): string
    {
        return implode(DIRECTORY_SEPARATOR, $pieces ?? []);
    }

    public function dumpRoles(): self
    {
        $roleStructure = $this->roleRepository->getRoleDataStructure();
        $filename = $this->buildPath(
            [
                $this->configurationFolder,
                UserManagementBundle::PACKAGE_FILENAME
            ]
        );
        $packageData = is_file($filename) ? Yaml::parse($filename) : ['parameters' => null, [UserManagementBundle::PACKAGE_NAME]];
        $packageData['parameters'] = $packageData['parameters'] ??= [];

        $parameterName = UserManagementBundle::PACKAGE_NAME . '.' . self::ROLES_PARAMETER;

        if ($this->env === 'prod') {
            $packageData['parameters'][$parameterName] = $roleStructure;
        } else {
            $packageData['when@' . $this->env]['parameters'][$parameterName] = $roleStructure;
        }

        $this->dumpSecurityConfiguration($filename, $packageData);

        return $this;
    }

    public function dumpAccess(): self
    {
        $accessStructure = $this->accessRepository->getAccessDataStructure();
        $filename = $this->buildPath(
            [
                $this->configurationFolder,
                UserManagementBundle::PACKAGE_FILENAME
            ]
        );
        $packageData = is_file($filename) ? Yaml::parse($filename) : ['parameters' => null, [UserManagementBundle::PACKAGE_NAME]];
        $packageData['parameters'] = $packageData['parameters'] ??= [];

        $parameterName = UserManagementBundle::PACKAGE_NAME . '.' . self::ACCESS_CONTROL_PARAMETER;

        if ($this->env === 'prod') {
            $packageData['parameters'][$parameterName] = $accessStructure;
        } else {
            $packageData['when@' . $this->env]['parameters'][$parameterName] = $accessStructure;
        }

        $this->dumpSecurityConfiguration($filename, $packageData);

        return $this;
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
