<?php

namespace GameBundle\Entity\Traits;

use GameBundle\Entity\User;

trait CreatorTrait
{
    /**
     * @ORM\ManyToOne(targetEntity="User", cascade={"persist"})
     * @ORM\JoinColumn(name="creator_id", referencedColumnName="id", onDelete="CASCADE", nullable=false)
     */
    private $creator;

    /**
     * @param User $creator
     *
     * @return $this
     */
    public function setCreator(User $creator)
    {
        $this->creator = $creator;

        return $this;
    }

    /**
     * @return User
     */
    public function getCreator()
    {
        return $this->creator;
    }

    /**
     * @return boolean
     */
    public function isCreator($id)
    {
        return $this->creator->getId() === $id;
    }

    public function hasCreatorTrait() {
        return true;
    }
}