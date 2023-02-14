<?php
/**
 * CoreSystems (c) 2023
 * Author: Josh McCreight<jmccreight@shaw.ca>
 */

declare(strict_types=1);

namespace CoreSys\UserManagement\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class Access
 * @package CoreSys\UserManagement\Entity
 */
#[ORM\Entity(repositoryClass: AccessRepository::class)]
#[ORM\Table(name: 'cs_access_map')]
class Access
{
    public const METHODS = ['GET', 'PUT', 'POST', 'DELETE', 'OPTION', 'LINK', 'UNLINK', 'HEAD'];

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $path = null;

    #[ORM\Column(type: Types::JSON, nullable: true)]
    private array $ips = [];

    #[ORM\Column(nullable: true)]
    private ?int $port = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $host = null;

    #[ORM\Column(type: Types::JSON, nullable: true)]
    private array $methods = [];

    #[ORM\Column(type: Types::JSON, nullable: true)]
    private array $attributes = [];

    #[ORM\Column(length: 128, nullable: true)]
    private ?string $route = null;

    #[ORM\ManyToMany(targetEntity: Role::class, inversedBy: 'access', cascade: ['persist'])]
    #[ORM\JoinTable]
    private Collection $roles;

    #[ORM\Column(type: Types::BOOLEAN)]
    private bool $enabled = true;

    #[ORM\Column(type: Types::BOOLEAN)]
    private bool $mandatory = false;

    #[ORM\Column(type: Types::BOOLEAN)]
    private bool $public = false;

    public function __construct()
    {
        $this->roles = new ArrayCollection();
        $this->methods = self::METHODS;
    }

    public function isMandatory(): bool
    {
        return $this->mandatory;
    }

    public function setMandatory(bool $mandatory = false): self
    {
        $this->mandatory = $mandatory;

        return $this;
    }

    public function isPublic(): bool
    {
        return $this->public ??= false;
    }

    public function setPublic(bool $public = false): self
    {
        $this->public = $public;

        return $this;
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    public function setEnabled(bool $enabled = true): self
    {
        $this->enabled = $enabled;

        return $this;
    }

    public function getRoles(): Collection
    {
        return $this->roles;
    }

    public function setRoles(Collection $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    public function addRole(Role $role): self
    {
        if (!$this->roles->contains($role)) {
            $this->roles->add($role);
            $role->addAccess($this);
        }

        return $this;
    }

    public function removeRole(Role $role): self
    {
        if ($this->roles->contains($role)) {
            $this->roles->removeElement($role);
            $role->removeAccess($this);
        }

        return $this;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPath(): ?string
    {
        return $this->path;
    }

    public function setPath(string $path): self
    {
        $this->path = $path;

        return $this;
    }

    public function getIps(): array
    {
        return $this->ips;
    }

    public function setIps(?array $ips = null): self
    {
        $this->ips = $ips ?? [];

        return $this;
    }

    public function getPort(): ?int
    {
        return $this->port;
    }

    public function setPort(?int $port): self
    {
        $this->port = $port;

        return $this;
    }

    public function getHost(): ?string
    {
        return $this->host;
    }

    public function setHost(?string $host): self
    {
        $this->host = $host;

        return $this;
    }

    public function getMethods(): array
    {
        return $this->methods;
    }

    public function setMethods(?array $methods): self
    {
        $this->methods = [];
        foreach ($methods as $method) {
            $this->addMethod($method);
        }

        return $this;
    }

    public function addMethod(string $method): self
    {
        $method = trim(strtoupper($method));
        if (
            in_array($method, self::METHODS) &&
            !in_array($method, $this->getMethods())
        ) {
            $this->methods[] = $method;
        }

        return $this;
    }

    public function removeMethod(string $method): self
    {
        $method = trim(strtoupper($method));
        $this->setMethods(
            array_filter(
                $this->getMethods(),
                function ($m) use ($method) {
                    return $m !== $method;
                }
            )
        );

        return $this;
    }

    public function getAttributes(): array
    {
        return $this->attributes;
    }

    public function setAttributes(?array $attributes = null): self
    {
        $this->attributes = $attributes ?? [];

        return $this;
    }

    public function getRoute(): ?string
    {
        return $this->route;
    }

    public function setRoute(?string $route): self
    {
        $this->route = $route;

        return $this;
    }

    public function serialize(): array
    {
        $serialized = [];
        $vars = get_class_vars(self::class);
        foreach (array_keys($vars) as $var) {
            if (method_exists($this, $method = sprintf('get%s', ucfirst($var)))) {
                $serialized[$var] = $this->$method();
            } elseif (method_exists($this, $method = sprintf('is%s', ucfirst($var)))) {
                $serialized[$var] = $this->$method();
            }
        }

        return $serialized;
    }

    public function output(): ?array
    {
        if (!($data = $this->serialize())['enabled']) {
            return null;
        }

        unset($data['id']);
        unset($data['enabled']);
        unset($data['mandatory']);

        $roles = [];
        foreach ($data['roles'] ?? [] as $role) {
            $roles[] = (string) $role;
        }
        $data['roles'] = $roles;

        if ($data['public']) {
            $data['roles'][] = 'PUBLIC_ACCESS';
        }
        unset($data['public']);

        foreach ($data as $key => $val) {
            if (null === $val || (is_array($val) && count($val) === 0)) {
                unset($data[$key]);
            }
        }

        return $data;
    }

    public function hasRoleName(string $roleName): bool
    {
        return in_array($roleName, ($this->output() ?? [])['roles'] ?? []);
    }

    public function hasRole(Role $role): bool
    {
        return $this->hasRoleName($role->getRoleName());
    }
}
