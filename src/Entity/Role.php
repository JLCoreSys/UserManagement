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
 * Class Role
 * @package CoreSys\UserManagement\Entity
 * @ORM\Entity()
 * @ORM\Table(name="cs_roles")
 */
class Role
{
    /**
     * @var string
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;
    /**
     * @var string
     * @ORM\Column(length=32, unique=true)
     */
    protected $name;
    /**
     * @var string
     * @ORM\Column(length=32, unique=true)
     */
    protected $roleName;
    /**
     * @var bool
     * @ORM\Column(type="boolean")
     */
    protected $mandatory;
    /**
     * @var bool
     * @ORM\Column(type="boolean")
     */
    protected $active;
    /**
     * @var Role|null
     * @ORM\ManyToOne(targetEntity="CoreSys\UserManagement\Entity\Role", inversedBy="children", cascade={"persist"})
     * @ORM\JoinColumn(name="parent_id", referencedColumnName="id", nullable=true, onDelete="SET NULL")
     */
    protected $parent;
    /**
     * @var Collection
     * @ORM\ManyToMany(targetEntity="CoreSys\UserManagement\Entity\Role", mappedBy="parent")
     */
    protected $children;
    /**
     * @var Collection
     * @ORM\ManyToMany(targetEntity="CoreSys\UserManagement\Entity\Access", mappedBy="roles")
     */
    protected $access;
    /**
     * @var Collection
     * @ORM\ManyToMany(targetEntity="CoreSys\UserManagement\Entity\User", mappedBy="systemRoles")
     */
    protected $users;
    /**
     * @var string
     * @ORM\Column(length=12)
     */
    protected $color;
    /**
     * @var bool
     * @ORM\Column(type="boolean")
     */
    protected $switch;

    /**
     * Role constructor.
     */
    public function __construct()
    {
        $this->mandatory = FALSE;
        $this->children = new ArrayCollection();
        $this->access = new ArrayCollection();
        $this->users = new ArrayCollection();
        $this->active = TRUE;
        $this->color = '#428BCA';
        $this->switch = FALSE;
    }

    /**
     * Get Id
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * Add a user
     *
     * @param User $user
     * @return Role
     */
    public function addUser( User $user ): Role
    {
        if ( !$this->users->contains( $user ) ) {
            $this->users->add( $user );
        }

        return $this;
    }

    /**
     * Remove a user
     *
     * @param User $user
     * @return Role
     */
    public function removeUser( User $user ): Role
    {
        if ( $this->users->contains( $user ) ) {
            $this->users->removeElement( $user );
        }

        return $this;
    }

    /**
     * Add an access
     *
     * @param Access $access
     * @return Role
     */
    public function addAccess( Access $access ): Role
    {
        if ( !$this->access->contains( $access ) ) {
            $access->addRole( $this );
            $this->access->add( $access );
        }

        return $this;
    }

    /**
     * Remove an access
     *
     * @param Access $access
     * @return Role
     */
    public function removeAccess( Access $access ): Role
    {
        if ( $this->access->contains( $access ) ) {
            $access->removeRole( $this );
            $this->access->removeElement( $access );
        }

        return $this;
    }

    /**
     * Add a child role
     *
     * @param Role $role
     * @return Role
     */
    public function addChild( Role $role ): Role
    {
        if ( !$this->children->contains( $role ) ) {
            $role->setParent( $this );
            $this->children->add( $role );
        }

        return $this;
    }

    /**
     * Remove a child role
     *
     * @param Role $role
     * @return Role
     */
    public function removeChild( Role $role ): Role
    {
        if ( $this->children->contains( $role ) ) {
            $role->setParent( NULL );
            $this->children->removeElement( $role );
        }

        return $this;
    }

    /**
     * Get Name
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * Set Name
     * @param string $name
     * @return Role
     */
    public function setName( string $name ): Role
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get RoleName
     * @return string|null
     */
    public function getRoleName(): ?string
    {
        return $this->roleName;
    }

    /**
     * Set RoleName
     * @param string $roleName
     * @return Role
     */
    public function setRoleName( string $roleName ): Role
    {
        $this->roleName = $roleName;

        return $this;
    }

    /**
     * Get Mandatory
     * @return bool
     */
    public function isMandatory(): bool
    {
        return $this->mandatory;
    }

    /**
     * Set Mandatory
     * @param bool $mandatory
     * @return Role
     */
    public function setMandatory( bool $mandatory ): Role
    {
        $this->mandatory = $mandatory;

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
     * @return Role
     */
    public function setActive( bool $active ): Role
    {
        $this->active = $active;

        return $this;
    }

    /**
     * Get Parent
     * @return Role|null
     */
    public function getParent(): ?Role
    {
        return $this->parent;
    }

    /**
     * Set Parent
     * @param Role|null $parent
     * @return Role
     */
    public function setParent( ?Role $parent ): Role
    {
        $this->parent = $parent;

        return $this;
    }

    /**
     * Get Children
     * @return Collection
     */
    public function getChildren(): Collection
    {
        return $this->children;
    }

    /**
     * Set Children
     * @param Collection $children
     * @return Role
     */
    public function setChildren( Collection $children ): Role
    {
        $this->children = $children;

        return $this;
    }

    /**
     * Get Access
     * @return Collection
     */
    public function getAccess(): Collection
    {
        return $this->access;
    }

    /**
     * Set Access
     * @param Collection $access
     * @return Role
     */
    public function setAccess( Collection $access ): Role
    {
        $this->access = $access;

        return $this;
    }

    /**
     * Get Users
     * @return Collection
     */
    public function getUsers(): Collection
    {
        return $this->users;
    }

    /**
     * Set Users
     * @param Collection $users
     * @return Role
     */
    public function setUsers( Collection $users ): Role
    {
        $this->users = $users;

        return $this;
    }

    /**
     * Get Color
     * @return string
     */
    public function getColor(): string
    {
        return $this->color;
    }

    /**
     * Set Color
     * @param string $color
     * @return Role
     */
    public function setColor( string $color ): Role
    {
        $this->color = $color;

        return $this;
    }

    /**
     * Get Switch
     * @return bool
     */
    public function isSwitch(): bool
    {
        return $this->switch;
    }

    /**
     * Set Switch
     * @param bool $switch
     * @return Role
     */
    public function setSwitch( bool $switch ): Role
    {
        $this->switch = $switch;

        return $this;
    }
}