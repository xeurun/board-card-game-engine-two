<?php

namespace GameBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

abstract class BaseEntity
{
    public function hasDateAtTrait() {
        return false;
    }

    public function hasCreatorTrait() {
        return false;
    }
}