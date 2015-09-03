<?php

namespace GameBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Room
 *
 * @ORM\Table(options={"comment": "Игрок"})
 * @ORM\Entity(repositoryClass="GameBundle\Repository\PlayerRepository")
 */
class Player extends BaseEntity implements \JsonSerializable
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
     * @ORM\ManyToOne(targetEntity="User", inversedBy="players", fetch="EAGER")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     **/
    private $user;

    /**
     * @ORM\ManyToOne(targetEntity="Room", inversedBy="players", fetch="EAGER")
     * @ORM\JoinColumn(name="room_id", referencedColumnName="id")
     **/
    private $room;

    /**
     * Очки игрока
     * @var string
     *
     * @ORM\Column(name="points", type="integer")
     */
    private $points = 0;

    /**
     * @ORM\OneToMany(targetEntity="CardIn", mappedBy="player", fetch="EAGER")
     **/
    private $cards;

    private $roomCards;

    /** ============================================================
     *                   DO NOT REMOVE THIS
     *  ============================================================ **/

    public function __construct(Room $room, User $user)
    {
        $this->room = $room;
        $this->user = $user;
        $this->roomCards = [];
    }

    function jsonSerialize()
    {
        return array(
            'id' => $this->id,
            'username' => $this->user->getUsername(),
            'points' => $this->getPoints(),
            'cards' => $this->roomCards
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
     * Set user
     *
     * @param User $user
     * @return Player
     */
    public function setUser(User $user)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set room
     *
     * @param Room $room
     * @return Player
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
     * Set points
     *
     * @param integer $points
     * @return Player
     */
    public function setCount($points)
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
     * Add points
     *
     * @param integer $points
     * @return Player
     */
    public function addPoints($points)
    {
        $this->points += $points;

        return $this;
    }

    /**
     * Add card
     *
     * @param \GameBundle\Entity\Card $card
     *
     * @return Player
     */
    public function addCard(\GameBundle\Entity\Card $card)
    {
        $this->cards->add($card);

        return $this;
    }

    /**
     * Remove card
     *
     * @param \GameBundle\Entity\Card $card
     *
     * @return Player
     */
    public function removeCard(\GameBundle\Entity\Card $card)
    {
        $this->cards->removeElement($card);

        return $this;
    }

    /**
     * Get cards
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getCards()
    {
        return $this->cards;
    }

    /**
     * Get cards
     * @param array $cardsIn
     *
     * @return Player
     */
    public function setRoomCards(array $cardsIn)
    {
        $this->roomCards = $cardsIn;

        return $this;
    }

    /**
     * Add card
     * @param CardIn $cardIn
     *
     * @return Player
     */
    public function addRoomCard($cardIn)
    {
        $this->roomCards[$cardIn->getId()] = $cardIn;

        return $this;
    }

    /**
     * Get cardIn
     * @param integer $cardInId
     *
     * @return CardIn|null
     */
    public function getRoomCard($cardInId, $delete = false)
    {
        if (isset($this->roomCards[(int)$cardInId])) {
            $card = $this->roomCards[(int)$cardInId];
            if($delete) {
                unset($this->roomCards[(int)$cardInId]);
            }

            return $card;
        }

        return null;
    }

    /**
     * Remove cardIn
     * @param integer $cardInId
     *
     * @return CardIn
     */
    public function removeRoomCard($cardInId)
    {
        unset($this->roomCards[(int)$cardInId]);

        return $this;
    }
}