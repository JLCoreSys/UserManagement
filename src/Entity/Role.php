<?php
/**
 * CoreSystems (c) 2023
 * Author: Josh McCreight<jmccreight@shaw.ca>
 */

declare(strict_types=1);

namespace CoreSys\UserManagement\Entity;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class Role
 * @package CoreSys\UserManagement\Entity
 */
#[ORM\Entity(repositoryClass: RoleRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Role
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 32)]
    private string $name = '';

    #[ORM\Column(length: 32)]
    private string $roleName = '';

    #[ORM\Column()]
    private bool $mandatory = false;

    #[ORM\Column()]
    private bool $enabled = true;

    #[ORM\ManyToMany(targetEntity: self::class, inversedBy: 'inheritedBy', cascade: ['persist'])]
    #[ORM\JoinTable()]
    #[ORM\JoinColumn(name: 'inherits_id', referencedColumnName: 'id')]
    #[ORM\InverseJoinColumn(name: 'inherited_id', referencedColumnName: 'id')]
    private Collection $inherits;

    #[ORM\ManyToMany(mappedBy: 'inherits', targetEntity: self::class)]
    private Collection $inheritedBy;

    #[ORM\ManyToMany(targetEntity: User::class, mappedBy: 'systemRoles')]
    private Collection $users;

    #[ORM\Column()]
    private string $color = '#428BCA';

    #[ORM\Column(nullable: true)]
    private bool $switch = false;

    #[ORM\Column(type: 'datetime')]
    private DateTime $createdAt;

    #[ORM\Column(type: 'datetime')]
    private DateTime $updatedAt;

    #[ORM\ManyToMany(targetEntity: Access::class, mappedBy: 'roles', cascade: ['persist'])]
    private Collection $access;

    public function __construct()
    {
        $this->inherits = new ArrayCollection();
        $this->inheritedBy = new ArrayCollection();
        $this->users = new ArrayCollection();
        $this->createdAt = new DateTime();
        $this->updatedAt = new DateTime();
        $this->access = new ArrayCollection();
    }

    public function getAccess(): Collection
    {
        return $this->access ??= new ArrayCollection();
    }

    public function setAccess(Collection $access): self
    {
        $this->access = $access;

        return $this;
    }

    public function addAccess(Access $access): self
    {
        if (!$this->access->contains($access)) {
            $this->access->add($access);
            $access->addRole($this);
        }

        return $this;
    }

    public function removeAccess(Access $access): self
    {
        if ($this->access->contains($access)) {
            $this->access->removeElement($access);
            $access->removeRole($this);
        }

        return $this;
    }

    public function getCreatedAt(): DateTime
    {
        return $this->createdAt ??= new DateTime();
    }

    public function getUpdatedAt(): DateTime
    {
        return $this->updatedAt ??= new DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name ??= '';
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getRoleName(): string
    {
        return $this->roleName ??= '';
    }

    public function setRoleName(string $roleName): self
    {
        $this->roleName = $roleName;

        return $this;
    }

    public function isMandatory(): bool
    {
        return $this->mandatory ??= false;
    }

    public function setMandatory(bool $mandatory): self
    {
        $this->mandatory = $mandatory;

        return $this;
    }

    public function isEnabled(): bool
    {
        return $this->enabled ??= true;
    }

    public function setEnabled(bool $enabled): self
    {
        $this->enabled = $enabled;

        return $this;
    }

    /**
     * @return Collection<int, self>
     */
    public function getInheritedBy(): Collection
    {
        return $this->inheritedBy;
    }

    public function addInheritedBy(self $role): self
    {
        if (!$this->inheritedBy->contains($role)) {
            $this->inheritedBy->add($role->addInherit($this));
        }

        return $this;
    }

    public function removeInheritedBy(self $role): self
    {
        if ($this->inheritedBy->contains($role)) {
            $this->inheritedBy->removeElement($role);
            $role->removeInherit($this);
        }

        return $this;
    }

    /**
     * @return Collection<int, self>
     **/
    public function getInherits(): Collection
    {
        return $this->inherits;
    }

    public function setInherits(Collection $roles): self
    {
        $this->inherits = $roles;

        return $this;
    }

    public function addInherit(self $role): self
    {
        if (!$this->inherits->contains($role)) {
            $this->inherits->add($role);
            $role->addInheritedBy($this);
        }

        return $this;
    }

    public function removeInherit(self $role): self
    {
        if ($this->inherits->contains($role)) {
            $this->inherits->removeElement($role);
            $role->removeInheritedBy($this);
        }

        return $this;
    }

    /**
     * @return Collection<int, User>
     */
    public function getUsers(): Collection
    {
        return $this->users;
    }

    public function setUsers(Collection $users): self
    {
        $this->users = $users;

        return $this;
    }

    public function addUser(User $user): self
    {
        if (!$this->users->contains($user)) {
            $this->users->add($user);
        }

        return $this;
    }

    public function removeUser(User $user): self
    {
        if ($this->users->contains($user)) {
            $this->users->removeElement($user);
        }

        return $this;
    }

    public function getColor(): string
    {
        return $this->color;
    }

    public function setColor(string $color): self
    {
        $this->color = $color;

        return $this;
    }

    public function getSwitch(): bool
    {
        return $this->switch ??= false;
    }

    public function setSwitch(bool $switch = false): self
    {
        $this->switch = $switch;

        return $this;
    }

    #[ORM\PrePersist]
    #[ORM\PreUpdate]
    #[ORM\PostLoad]
    public function prePersistAndLoad(): void
    {
        $roleName = self::nameToRoleName($this->getName());

        if ($this->getRoleName() !== $roleName) {
            $this->setRoleName($roleName);
        }
    }

    #[ORM\PrePersist]
    #[ORM\PreUpdate]
    public function prePersist(): void
    {
        $this->getCreatedAt();
        $this->updatedAt = new DateTime();
    }

    /**
     * Convert plain string name to roleName format
     * `ROLE_[A-Z0-9_]`
     *
     * Examples:
     *  - `user` => `ROLE_USER`
     *  - `Super Admin` => `ROLE_SUPER_ADMIN`
     *  - `Some other role name` => `ROLE_SOME_OTHER_ROLE_NAME`
     *  - `user-2` => `ROLE_USER_2`
     *  - `user 2 @` => `ROLE_USER_2`
     *
     * @param string $name
     * @return string
     */
    public static function nameToRoleName(string $name): string
    {
        $roleName = preg_replace('/(ROLE_|[^\sA-Z0-9_-]+)/', '', strtoupper($name));
        $roleName = str_replace(['-', ' '], ['_', '_'], $roleName);

        while (str_contains($roleName, '__')) {
            $roleName = str_replace('__', '_', $roleName);
        }

        return sprintf(
            'ROLE_%s',
            trim($roleName, ' _')
        );
    }

    public function getRoleNames(): array
    {
        $roleNames = ['ROLE_USER'];

        if ($this->getSwitch()) {
            $roleNames[] = 'ROLE_ALLOWED_TO_SWITCH';
        }

        foreach ($this->getInherits() as $role) {
            $roleNames = array_merge($roleNames, $role->getRoleNames());
        }

        return array_unique(
            array_merge($roleNames, [$this->getRoleName()])
        );
    }

    public function isRole(string $role): bool
    {
        return in_array(
            self::nameToRoleName($role),
            $this->getRoleNames()
        );
    }

    public function __toString(): string
    {
        return $this->getRoleName() ?? '';
    }
}
