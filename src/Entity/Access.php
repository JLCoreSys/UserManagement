<?php
/**
 * CoreSystems (c) 2020
 * Author: Josh McCreight<jmccreight@shaw.ca>
 */

declare( strict_types = 1 );

namespace CoreSys\UserManagement\Entity;

use CoreSys\UserManagement\Entity\Traits\Id;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class Access
 * @package CoreSys\UserManagement\Entity
 * @ORM\Entity()
 * @ORM\Table(name="cs_access")
 * @ORM\EntityListeners({"CoreSys\UserManagement\Entity\Listeners\AccessListener"})
 */
class Access
{
    use Id;

    /**
     * @var string
     * @ORM\Column(length=255, unique=true)
     */
    protected $path;

    /**
     * @var Collection
     * @ORM\ManyToMany(targetEntity="CoreSys\UserManagement\Entity\Role", inversedBy="access", cascade={"persist"})
     * @ORM\JoinTable(name="cs_access_roles")
     */
    protected $roles;

    /**
     * @var bool
     * @ORM\Column(type="boolean")
     */
    protected $active;

    /**
     * @var string|null
     * @ORM\Column(length=255, nullable=true)
     */
    protected $host;

    /**
     * @var string|null
     * @ORM\Column(length=32, nullable=true)
     */
    protected $ip;

    /**
     * @var bool
     * @ORM\Column(type="boolean")
     */
    protected $anonymous;

    /**
     * @var array
     * @ORM\Column(type="array")
     */
    protected $methods;

    /**
     * @var string|null
     * @ORM\Column(length=32, nullable=true)
     */
    protected $channel;

    /**
     * Access constructor.
     */
    public function __construct()
    {
        $this->active = TRUE;
        $this->anonymous = FALSE;
        $this->roles = new ArrayCollection();
        $this->methods = [ 'GET', 'POST', 'PUT', 'DELETE', 'PATCH', 'HEAD', 'OPTIONS' ];
    }

    /**
     * Get Path
     * @return string
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * Set Path
     * @param string $path
     * @return Access
     */
    public function setPath( string $path ): Access
    {
        $this->path = $path;

        return $this;
    }

    /**
     * Get Roles
     * @return Collection
     */
    public function getRoles(): Collection
    {
        return $this->roles;
    }

    /**
     * Set Roles
     * @param Collection $roles
     * @return Access
     */
    public function setRoles( Collection $roles ): Access
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * Get Active
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->active;
    }

    /**
     * Set Active
     * @param bool $active
     * @return Access
     */
    public function setActive( bool $active ): Access
    {
        $this->active = $active;

        return $this;
    }

    /**
     * Get Host
     * @return null|string
     */
    public function getHost(): ?string
    {
        return $this->host;
    }

    /**
     * Set Host
     * @param null|string $host
     * @return Access
     */
    public function setHost( ?string $host ): Access
    {
        $this->host = $host;

        return $this;
    }

    /**
     * Get Ip
     * @return null|string
     */
    public function getIp(): ?string
    {
        return $this->ip;
    }

    /**
     * Set Ip
     * @param null|string $ip
     * @return Access
     */
    public function setIp( ?string $ip ): Access
    {
        $this->ip = $ip;

        return $this;
    }

    /**
     * Get Anonymous
     * @return bool
     */
    public function isAnonymous(): bool
    {
        return $this->anonymous;
    }

    /**
     * Set Anonymous
     * @param bool $anonymous
     * @return Access
     */
    public function setAnonymous( bool $anonymous ): Access
    {
        $this->anonymous = $anonymous;

        return $this;
    }

    /**
     * Get Methods
     * @return array
     */
    public function getMethods(): array
    {
        return $this->methods;
    }

    /**
     * Set Methods
     * @param array $methods
     * @return Access
     */
    public function setMethods( array $methods ): Access
    {
        $this->methods = $methods;

        return $this;
    }

    /**
     * Get Channel
     * @return null|string
     */
    public function getChannel(): ?string
    {
        return $this->channel;
    }

    /**
     * Set Channel
     * @param null|string $channel
     * @return Access
     */
    public function setChannel( ?string $channel ): Access
    {
        $this->channel = $channel;

        return $this;
    }

    /**
     * @param Role $role
     * @return Access
     */
    public function addRole( Role $role ): Access
    {
        if ( !$this->roles->contains( $role ) ) {
            // Add this access to the role
            $role->addAccess( $this );

            // Add the role to the roles list
            $this->roles->add( $role );
        }

        return $this;
    }

    /**
     * @param Role $role
     * @return Access
     */
    public function removeRole( Role $role ): Access
    {
        // Remove this access from the role, regardless if its part of this Access or not
        $role->removeAccess( $this );

        if ( $this->roles->contains( $role ) ) {
            $this->roles->removeElement( $role );
        }

        return $this;
    }
}