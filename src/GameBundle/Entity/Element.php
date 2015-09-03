<?php

namespace GameBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Card
 *
 * @ORM\Table(options={"comment": "Элемент"})
 * @ORM\Entity(repositoryClass="GameBundle\Repository\ElementRepository")
 */
class Element extends BaseEntity implements \JsonSerializable
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * Ключ
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=100, unique=true, nullable=false)
     */
    private $code;

    /**
     * Ключ
     * @var string
     *
     * @ORM\Column(name="type", type="string", length=50, nullable=true)
     */
    private $type;

    /**
     * Ключ
     * @var string
     *
     * @ORM\Column(name="caption", type="string", length=150, nullable=true)
     */
    private $caption;

    public function __construct($code)
    {
        $this->code = $code;
    }

    function jsonSerialize()
    {
        return array(
            'id' => $this->id,
            'code' => $this->code,
            'caption' => $this->caption,
            'type' => $this->type,
        );
    }

    public function __toString()
    {
        return $this->code;
    }

    /** ============================================================
     *  IF YOU WANT TO REGENERATE THE ENTITY, PLEASE CLEAR ALL BELOW
     *  ============================================================ **/

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set code
     *
     * @param string $code
     *
     * @return Card
     */
    public function setCode($code)
    {
        $this->code = $code;

        return $this;
    }

    /**
     * Get code
     *
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $type
     *
     * @return Card
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @return string
     */
    public function getCaption()
    {
        return $this->caption;
    }

    /**
     * @param string $caption
     *
     * @return Card
     */
    public function setCaption($caption)
    {
        $this->caption = $caption;

        return $this;
    }
}