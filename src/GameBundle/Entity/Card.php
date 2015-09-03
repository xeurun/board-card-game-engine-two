<?php

namespace GameBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use GameBundle\Constants\CardType;

/**
 * Card
 *
 * @ORM\Table(options={"comment": "Карточка"})
 * @ORM\Entity(repositoryClass="GameBundle\Repository\CardRepository")
 */
class Card extends BaseEntity implements \JsonSerializable
{
    use \GameBundle\Entity\Traits\DateAtTrait;

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
     * @ORM\Column(name="name", type="string", length=50, unique=true, nullable=false)
     */
    private $name;

    /**
     * Тип
     * @var string
     *
     * @ORM\Column(name="type", type="string", length=100, nullable=false)
     */
    private $type;

    /**
     * Описание
     * @var string
     *
     * @ORM\Column(name="caption", type="string", length=100, nullable=false)
     */
    private $caption;

    /**
     * Очки карточки
     * @var string
     *
     * @ORM\Column(name="points", type="integer")
     */
    private $points;

    /**
     * @ORM\ManyToMany(targetEntity="Element", cascade={"persist"})
     * @ORM\JoinTable(name="card_has_requirements",
     *      joinColumns={@ORM\JoinColumn(name="card_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="element_id", referencedColumnName="id")}
     * )
     */
    private $requirements;

    /**
     * @ORM\ManyToMany(targetEntity="Element", cascade={"persist"})
     * @ORM\JoinTable(name="card_has_ingridients",
     *      joinColumns={@ORM\JoinColumn(name="card_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="element_id", referencedColumnName="id")}
     * )
     */
    private $ingridients;

    /**
     * Описание
     * @var string
     *
     * @ORM\Column(name="file", type="string", length=1000, nullable=false)
     */
    private $file;

    /**
     * Колода
     * @var string
     *
     * @ORM\Column(name="deck", type="string", length=50, nullable=true)
     */
    private $deck;

    /**
     * Кол-во карт в колоде
     * @var string
     *
     * @ORM\Column(name="count", type="integer")
     */
    private $count;

    /** ============================================================
     *                   DO NOT REMOVE THIS
     *  ============================================================ **/

    public function __construct() {
        $this->requirements = new \Doctrine\Common\Collections\ArrayCollection();
        $this->ingridients = new \Doctrine\Common\Collections\ArrayCollection();
    }

    function jsonSerialize()
    {
        return array(
            'id' => $this->id,
            'name' => $this->name,
            'type' => $this->type,
            'caption' => $this->caption,
            'points' => $this->points
        );
    }

    public function __toString()
    {
        return $this->name;
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
     * Set name
     *
     * @param string $name
     *
     * @return Card
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set type
     *
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
     * Get type
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set caption
     *
     * @param string $caption
     *
     * @return Card
     */
    public function setCaption($caption)
    {
        $this->caption = $caption;

        return $this;
    }

    /**
     * Get caption
     *
     * @return string
     */
    public function getCaption()
    {
        return $this->caption;
    }

    /**
     * Set points
     *
     * @param integer $points
     *
     * @return Card
     */
    public function setPoints($points)
    {
        $this->points = $points;

        return $this;
    }

    /**
     * Get points
     *
     * @return integer
     */
    public function getPoints()
    {
        return $this->points;
    }

    /**
     * Add requirement
     *
     * @param Element $requirement
     *
     * @return Card
     */
    public function addRequirement(Element $requirement)
    {
        $this->requirements->add($requirement);

        return $this;
    }

    /**
     * Remove requirement
     *
     * @param Element $requirement
     *
     * @return Card
     */
    public function removeRequirement(Element $requirement)
    {
        $this->requirements->remove($requirement);

        return $this;
    }

    /**
     * Get requirements
     *
     * @return ArrayCollection
     */
    public function getRequirements()
    {
        return $this->requirements;
    }

    /**
     * Add ingridient
     *
     * @param Element $ingridient
     *
     * @return Card
     */
    public function addIngridient(Element $ingridient)
    {
        $this->ingridients->add($ingridient);

        return $this;
    }

    /**
     * Remove ingridient
     *
     * @param Element $ingridient
     *
     * @return Card
     */
    public function removeIngridient(Element $ingridient)
    {
        $this->ingridients->remove($ingridient);

        return $this;
    }

    /**
     * Get ingridients
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getIngridients()
    {
        return $this->ingridients;
    }

    /**
     * Set file
     *
     * @param string $file
     *
     * @return Card
     */
    public function setFile($file)
    {
        $this->file = $file;

        return $this;
    }

    /**
     * Get file
     *
     * @return string
     */
    public function getFile()
    {
        return $this->file;
    }

    /**
     * Set deck
     *
     * @param string $deck
     *
     * @return Card
     */
    public function setDeck($deck)
    {
        $this->deck = $deck;

        return $this;
    }

    /**
     * Get deck
     *
     * @return string
     */
    public function getDeck()
    {
        return $this->deck;
    }

    /**
     * Set count
     *
     * @param integer $count
     * @return Card
     */
    public function setCount($count)
    {
        $this->count = $count;

        return $this;
    }

    /**
     * Get count
     *
     * @return integer
     */
    public function getCount()
    {
        return $this->count;
    }
}