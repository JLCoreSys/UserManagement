<?php
/**
 * CoreSystems (c) 2020
 * Author: Josh McCreight<jmccreight@shaw.ca>
 */

declare( strict_types = 1 );

namespace CoreSys\UserManagement\Entity\Traits;

/**
 * Trait Id
 * @package CoreSys\UserManagement\Entity\Traits
 */
trait Id
{
    /**
     * @var string
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * Get Id
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }
}