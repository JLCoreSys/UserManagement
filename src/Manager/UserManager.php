<?php
/**
 * CoreSystems (c) 2020
 * Author: Josh McCreight<jmccreight@shaw.ca>
 */

declare( strict_types = 1 );

namespace CoreSys\UserManagement\Manager;

use CoreSys\UserManagement\Entity\User;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserManager extends AbstractManager
{
    /**
     * @var UserPasswordEncoderInterface
     */
    protected $passwordEncoder;

    /**
     * UserManager constructor.
     * @param UserPasswordEncoderInterface $passwordEncoder
     */
    public function __construct( UserPasswordEncoderInterface $passwordEncoder )
    {
        $this->passwordEncoder = $passwordEncoder;
    }

    /**
     * @param User $user
     */
    public function remove( User &$user )
    {
        // @todo
    }

    /**
     * @param User $user
     */
    public function update( User &$user )
    {
        $this->updatePassword( $user );
    }

    /**
     * If plain password has a value, then update the users password
     *
     * @param User $user
     */
    protected function updatePassword( User &$user )
    {
        $plainPassword = $user->getPlainPassword();
        if ( !empty( $plainPassword ) ) {
            $user->setPassword( $this->passwordEncoder->encodePassword(
                $user,
                $plainPassword
            ) )->setPlainPassword( NULL );
        }
    }
}