<?php
/**
 * CoreSystems (c) 2020
 * Author: Josh McCreight<jmccreight@shaw.ca>
 */

declare( strict_types = 1 );

namespace CoreSys\UserManagement\Manager\Traits;

use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\KernelInterface;

trait ConfigurationYaml
{
    /**
     * @var string|null
     */
    protected $file;

    /**
     * @return $this
     */
    public function dumpYaml(): self
    {
        if ( !method_exists( $this, 'getDataStructure' ) ) {
            throw new Exception( 'Implement getDataStructure' );
        }

        $yaml = $this->getDataStructure( TRUE );
        dump( $yaml );
        exit;

        return $this->writeYamlToFile( $yaml );
    }

    /**
     * @param mixed $yaml
     * @return $this
     */
    public function writeYamlToFile( $yaml = NULL ): self
    {
        if ( $yaml === NULL ) {
            $fp = fopen( $this->getFile(), 'w+' );
            if ( $fp ) {
                fclose( $fp );
            }
        } else {
            $fs = new Filesystem();
            $fs->dumpFile( $this->getFile(), $yaml, 0777 );
        }

        return $this;
    }

    /**
     * Get File
     * @return null|string
     */
    public function getFile(): ?string
    {
        return $this->file;
    }

    /**
     * Set File
     * @param null|string $file
     * @return $this
     */
    public function setFile( ?string $file ): self
    {
        $this->file = $file;

        return $this;
    }

    /**
     * Get the full path to the configuration file for the given filename
     *
     * @param string $filename
     * @return string
     */
    public function getConfigurationFile( string $filename ): string
    {
        if ( empty( $this->kernel ) ) {
            throw new Exception( 'Kernel is required' );
        }

        return implode( DIRECTORY_SEPARATOR, [
            $this->getKernel()->getProjectDir(),
            'config',
            $filename
        ] );

    }

}