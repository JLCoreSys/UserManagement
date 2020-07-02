<?php
/**
 * CoreSystems (c) 2020
 * Author: Josh McCreight<jmccreight@shaw.ca>
 */

declare( strict_types = 1 );

namespace CoreSys\UserManagement\Entity;

use CoreSys\UserManagement\Entity\Traits\Id;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Security\Core\User\UserInterface;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class User
 * @package CoreSys\UserManagement\Entity
 * @ORM\Entity()
 * @ORM\Table(name="cs_user")
 * @ORM\DiscriminatorColumn(name="discr", type="string")
 * @ORM\DiscriminatorMap({
 *     "base"="CoreSys\UserManagement\Entity\User"
 *     })
 * @ORM\EntityListeners({"CoreSys\UserManagement\Entity\Listeners\UserListener"})
 */
class User implements UserInterface
{

    use Id;

    /**
     * @var string
     * @ORM\Column(name="email", length=128)
     */
    protected $email;

    /**
     * @var string
     * @ORM\Column(name="password", length=128)
     */
    protected $password;

    /**
     * @var string|null
     */
    protected $plainPassword;

    /**
     * @var string
     * @ORM\Column(name="salt", length=64)
     */
    protected $salt;

    /**
     * @var array
     * @ORM\Column(name="roles", type="array")
     */
    protected $roles;

    /**
     * @var Collection
     * @ORM\ManyToMany(targetEntity="CoreSys\UserManagement\Entity\Role", inversedBy="users",
     *                                                                    cascade={"remove","persist"})
     * @ORM\JoinTable(name="cs_user_roles")
     */
    protected $systemRoles;

    /**
     * User constructor.
     */
    public function __construct()
    {
        $this->setSalt( base_convert( sha1( uniqid( mt_rand(), TRUE ) ), 16, 36 ) );
        $this->setRoles( [ 'ROLE_USER' ] );
    }

    /**
     * Erase Credentials
     */
    public function eraseCredentials()
    {
        $this->setPlainPassword( NULL );

        return $this;
    }

    /**
     * Get Password
     * @return string
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    /**
     * Set Password
     * @param string $password
     * @return User
     */
    public function setPassword( string $password ): User
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Get Roles
     * @return array
     */
    public function getRoles(): array
    {
        return $this->roles;
    }

    /**
     * Set Roles
     * @param array $roles
     * @return User
     */
    public function setRoles( array $roles ): User
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * Get Salt
     * @return string
     */
    public function getSalt(): string
    {
        return $this->salt;
    }

    /**
     * Set Salt
     * @param string $salt
     * @return User
     */
    public function setSalt( string $salt ): User
    {
        $this->salt = $salt;

        return $this;
    }

    public function getUsername(): string
    {
        return $this->getEmail();
    }

    /**
     * Get Email
     * @return string
     */
    public function getEmail(): string
    {
        return $this->email;
    }

    /**
     * Set Email
     * @param string $email
     * @return User
     */
    public function setEmail( string $email ): User
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Add a role to the array
     *
     * @param string $roleName
     * @return User
     */
    public function addRole( string $roleName ): User
    {
        if ( !in_array( $roleName = strtoupper( $roleName ), $this->roles ) ) {
            $this->roles[] = $roleName;
        }

        return $this;
    }

    /**
     * Remove a role
     *
     * @param string $roleName
     * @return User
     */
    public function removeRole( string $roleName ): User
    {
        if ( in_array( $roleName = strtoupper( $roleName ), $this->roles ) ) {
            $roles = [];
            foreach ( $this->roles as $role ) {
                if ( $role !== $roleName ) {
                    $roles[] = $role;
                }
            }
            $this->setRoles( $roles );
        }

        return $this;
    }

    /**
     * Get PlainPassword
     * @return null|string
     */
    public function getPlainPassword(): ?string
    {
        return $this->plainPassword;
    }

    /**
     * Set PlainPassword
     * @param null|string $plainPassword
     * @return User
     */
    public function setPlainPassword( ?string $plainPassword ): User
    {
        $this->plainPassword = $plainPassword;

        return $this;
    }

}