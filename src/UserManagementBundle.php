<?php

/**
 * CoreSystems (c) 2023
 * Author: Josh McCreight<jmccreight@shaw.ca>
 */

declare(strict_types=1);

namespace CoreSys\UserManagement;

use CoreSys\UserManagement\DependencyInjection\Compiler\AccessCompilerPass;
use CoreSys\UserManagement\DependencyInjection\Compiler\RolesCompilerPass;
use Exception;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\Yaml\Yaml;

/**
 * Class UserManagementBundle
 * @package CoreSys\UserManagement
 */
class UserManagementBundle extends Bundle
{
    public const PACKAGE_FILENAME = 'coresys.yaml';
    public const PACKAGE_NAME = 'coresys_user_management';

    public function build(ContainerBuilder $builder)
    {
        parent::build($builder);
        $builder->addCompilerPass(new RolesCompilerPass());
        $builder->addCompilerPass(new AccessCompilerPass());
    }

    public static function install(): void
    {
        $packageFolder = self::getPackageFolder();
        if (empty($packageFolder)) {
            throw new Exception('Could not locate packages folder.');
        }

        $fs = new Filesystem();

        $filename = self::buildPath([$packageFolder, self::PACKAGE_FILENAME]);
        if (!is_file($filename)) {
            // file does not exist - create an empty schema with defaults
            $defaults = self::getDefaultPackageContents() ?? [];
            $fs->dumpFile($filename, Yaml::dump($defaults, 4, 2));
        }
    }

    private static function getPackageFolder(): ?string
    {
        $folder = self::buildPath(['config', 'packages']);
        $base = dirname(__DIR__);
        $count = 10;
        while ($count-- >= 0 && !is_dir($packages = self::buildPath([$base, $folder]))) {
            $base = dirname($base);
        }

        return is_dir($packages) ? $packages : null;
    }

    private static function getDefaultPackageContents(): array
    {
        $defaultsFile = self::buildPath([__DIR__, 'Resources', 'coresys.yaml']);

        if (!is_file($defaultsFile)) {
            throw new Exception(sprintf('Could not find file `%s`', $defaultsFile));
        }

        return Yaml::parse(file_get_contents($defaultsFile));
    }

    private static function buildPath(array $pieces): string
    {
        return implode(DIRECTORY_SEPARATOR, $pieces);
    }
}
