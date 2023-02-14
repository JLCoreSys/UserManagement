<?php
/**
 * CoreSystems (c) 2023
 * Author: Josh McCreight<jmccreight@shaw.ca>
 */

declare(strict_types=1);

namespace CoreSys\UserManagement\Entity;

use CoreSys\ReverseDiscriminator\Annotations\DiscriminatorEntry;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Class User
 * @package CoreSys\UserManagement\Entity
 */
#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: "cs_user")]
#[ORM\Index(name: "password_idx", columns: ["password"])]
#[ORM\InheritanceType("SINGLE_TABLE")]
#[ORM\DiscriminatorColumn(name: "discr", type: "string", length: 32)]
#[ORM\HasLifecycleCallbacks()]
#[DiscriminatorEntry("user")]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\ManyToMany(targetEntity: Role::class, inversedBy: "users", cascade: ["remove", "persist"])]
    #[ORM\JoinTable(name: 'cs_user_roles')]
    protected Collection|ArrayCollection $systemRoles;

    #[ORM\Column(type: 'datetime')]
    private DateTime $createdAt;

    #[ORM\Column(type: 'datetime')]
    private DateTime $updatedAt;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180, unique: true)]
    private ?string $email = null;

    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    private ?string $password = null;

    #[ORM\Column]
    private array $roles = [];

    /**
     * @var string|null plain text password for resetting
     */
    private ?string $plainPassword = null;

    public function __construct()
    {
        $this->setRoles(['ROLE_USER']);
        $this->systemRoles = new ArrayCollection();
        $this->createdAt = new DateTime();
        $this->updatedAt = new DateTime();
    }

     /**
     * Erase Credentials
     */
    public function eraseCredentials(): self
    {
        $this->setPlainPassword(null);

        return $this;
    }

    /**
     * Get the value of createdAt
     *
     * @return DateTime
     */
    public function getCreatedAt(): DateTime
    {
        return $this->createdAt;
    }

    /**
     * Set the value of createdAt
     *
     * @param DateTime $createdAt
     *
     * @return self
     */
    public function setCreatedAt(DateTime $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * Get the value of updatedAt
     *
     * @return DateTime
     */
    public function getUpdatedAt(): DateTime
    {
        return $this->updatedAt;
    }

    /**
     * Set the value of updatedAt
     *
     * @param DateTime $updatedAt
     *
     * @return self
     */
    public function setUpdatedAt(DateTime $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * Get the system roles
     *
     * @return Collection|ArrayCollection
     */
    public function getSystemRoles(): Collection|ArrayCollection
    {
        return $this->systemRoles;
    }

    /**
     * Set the system roles
     *
     * @param Collection|ArrayCollection $systemRoles
     * @return self
     */
    public function setSystemRoles(Collection $systemRoles): self
    {
        $this->systemRoles = $systemRoles;

        return $this;
    }

    /**
     * Add a system role
     *
     * @param Role $systemRole
     * @return self
     */
    public function addSystemRole(Role $systemRole): self
    {
        if (!$this->systemRoles->contains($systemRole)) {
            $this->systemRoles->add($systemRole->addUser($this));
            $this->addRole($systemRole->getRoleName());
        }

        return $this;
    }

    /**
     * Remove a system role
     *
     * @param Role $systemRole
     * @return self
     */
    public function removeSystemRole(Role $systemRole): self
    {
        if ($this->systemRoles->contains($systemRole)) {
            $this->systemRoles->removeElement($systemRole->removeUser($this));
            $this->removeRole($systemRole->getRoleName());
        }

        return $this;
    }

    /**
     * Has a systm role by name
     *
     * @param string $roleName
     * @return boolean
     */
    public function hasSystemRoleName(string $roleName): bool
    {
        $what = $this->getSystemRoles()->filter(function ($systemRole) use ($roleName) {
            if ($systemRole->getRoleName() === $roleName) {
                return $systemRole;
            }
            return false;
        });

        return $what->count() > 0;
    }

    /**
     * Get Id
     *
     * @return int
     */
    public function getId(): int
    {
        return $this->id ??= 0;
    }

    /**
     * Get Password
     *
     * @return string
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    /**
     * Set Password
     *
     * @param string $password
     * @return self
     */
    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Get Roles
     *
     * @return array
     */
    public function getRoles(): array
    {
        return array_unique(
            array_merge(
                ['ROLE_USER'],
                $this->roles ??= []
            )
        );
    }

    /**
     * Set Roles
     *
     * @param array $roles
     * @return self
     */
    public function setRoles(array $roles): self
    {
        $this->roles = array_unique(
            array_merge(
                ['ROLE_USER'],
                $this->roles ??= []
            )
        );
        ;

        return $this;
    }

    /**
     * Get Email
     *
     * @return string
     */
    public function getEmail(): string
    {
        return $this->email ??= '';
    }

    /**
     * Set Email
     *
     * @param string $email
     * @return self
     */
    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Add a role to the array
     *
     * @param string $roleName
     * @return self
     */
    public function addRole(string $roleName): self
    {
        if (!in_array($roleName = strtoupper($roleName), $this->roles)) {
            $this->roles[] = $roleName;
        }

        return $this;
    }

    /**
     * Remove a role
     *
     * @param string $roleName
     * @return self
     */
    public function removeRole(string $roleName): self
    {
        if (in_array($roleName = strtoupper($roleName), $this->roles)) {
            $roles = [];
            foreach ($this->roles as $role) {
                if ($role !== $roleName) {
                    $roles[] = $role;
                }
            }
            $this->setRoles($roles);
        }

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return $this->getEmail();
    }

    /**
     * Get the value of plainPassword
     *
     * @return ?string
     */
    public function getPlainPassword(): ?string
    {
        return $this->plainPassword;
    }

    /**
     * Set the value of plainPassword
     *
     * @param ?string $plainPassword
     *
     * @return self
     */
    public function setPlainPassword(?string $plainPassword): self
    {
        $this->plainPassword = $plainPassword;

        return $this;
    }

    #[ORM\PrePersist]
    #[ORM\PreUpdate]
    public function prePersist(): void
    {
        $this->getCreatedAt();
        $this->updatedAt = new DateTime();
    }
}
