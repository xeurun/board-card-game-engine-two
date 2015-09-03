<?php

namespace GameBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Room
 *
 * @ORM\Table(options={"comment": "Комнаты системы"})
 * @ORM\Entity(repositoryClass="GameBundle\Repository\CardInRepository")
 */
class CardIn extends BaseEntity implements \JsonSerializable
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
     * @ORM\ManyToOne(targetEntity="Card")
     * @ORM\JoinColumn(name="card_id", referencedColumnName="id", nullable=false)
     **/
    private $card;

    /**
     * Карта собранный рецепт
     * @var boolean
     *
     * @ORM\Column(name="recipe", type="boolean")
     */
    private $recipe = false;

    /**
     * @ORM\ManyToOne(targetEntity="CardIn")
     * @ORM\JoinColumn(name="parent_id", referencedColumnName="id", nullable=true)
     **/
    private $parent;

    /**
     * @ORM\ManyToOne(targetEntity="Room", inversedBy="cards")
     * @ORM\JoinColumn(name="room_id", referencedColumnName="id", nullable=false)
     **/
    private $room;

    /**
     * @ORM\ManyToOne(targetEntity="Player", inversedBy="cards")
     * @ORM\JoinColumn(name="player_id", referencedColumnName="id")
     **/
    private $player;

    /**
     * Очки игрока
     * @var boolean
     *
     * @ORM\Column(name="onTable", type="boolean")
     */
    private $onTable = false;

    /** ============================================================
     *                   DO NOT REMOVE THIS
     *  ============================================================ **/

    public function __construct(Card $card, Room $room)
    {
        $this->card = $card;
        $this->room = $room;
    }

    function jsonSerialize()
    {
        return array(
            'id' => $this->id,
            'url' => $this->card->getFile(),
            'type' => $this->card->getType(),
            'caption' => $this->card->getCaption(),
            'requirements' => $this->card->getRequirements()->toArray(),
            'ingridients' => $this->card->getIngridients()->toArray(),
            'recipe' => $this->recipe,
            'parent' => $this->parent instanceof CardIn ? $this->parent->getId() : null
        );
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
     * Set card
     *
     * @param \GameBundle\Entity\Card $card
     *
     * @return CardIn
     */
    public function setCard(\GameBundle\Entity\Card $card)
    {
        $this->card = $card;

        return $this;
    }

    /**
     * Get card
     *
     * @return Card
     */
    public function getCard()
    {
        return $this->card;
    }

    /**
     * Set parent
     *
     * @param CardIn $parent
     *
     * @return CardIn
     */
    public function setParent(CardIn $parent)
    {
        $this->parent = $parent;

        return $this;
    }

    /**
     * Get parent
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * Set room
     *
     * @param Room $room
     *
     * @return CardIn
     */
    public function setRoom(Room $room)
    {
        $this->room = $room;

        return $this;
    }

    /**
     * Get room
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getRoom()
    {
        return $this->room;
    }

    /**
     * Set player
     *
     * @param Player|null $player
     *
     * @return CardIn
     */
    public function setPlayer(Player $player = null)
    {
        $this->player = $player;

        return $this;
    }

    /**
     * Get player
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getPlayer()
    {
        return $this->player;
    }

    /**
     * @return boolean
     */
    public function isOnTable()
    {
        return $this->onTable;
    }

    /**
     * @param boolean $onTable
     *
     * @return CardIn
     */
    public function setOnTable($onTable = true)
    {
        $this->onTable = $onTable;

        return $this;
    }

    /**
     * @return boolean
     */
    public function isRecipe()
    {
        return $this->recipe;
    }

    /**
     * @param boolean $recipe
     *
     * @return CardIn
     */
    public function setRecipe($recipe = true)
    {
        $this->recipe = $recipe;

        return $this;
    }
}