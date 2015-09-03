<?php

namespace GameBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Ratchet\ConnectionInterface;

/**
 * Room
 *
 * @ORM\Table(options={"comment": "Комнаты системы"})
 * @ORM\Entity(repositoryClass="GameBundle\Repository\RoomRepository")
 */
class Room extends BaseEntity
{
    use \GameBundle\Entity\Traits\DateAtTrait;
    use \GameBundle\Entity\Traits\CreatorTrait;

    const STATUS_PREPARE    = 'prepare';
    const STATUS_PLAY       = 'play';

    const PHASE_GET     = 'get';
    const PHASE_TURN    = 'turn';

    const START_CARDS_ON_TABLE = 4;
    const START_CARDS_IN_HANDS = 4;

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * Уникальный hash комнаты
     * @var string
     *
     * @ORM\Column(name="hash", type="string", length=32, unique=true, nullable=true)
     */
    private $hash;

    /**
     * @ORM\OneToMany(targetEntity="Player", mappedBy="room", cascade={"all"}, fetch="EAGER")
     **/
    private $players;

    /**
     * @ORM\ManyToOne(targetEntity="Player", cascade={"all"})
     * @ORM\JoinColumn(name="turn_id", referencedColumnName="id", onDelete="CASCADE", nullable=true)
     */
    private $turn;

    /**
     * Статус комнаты
     * @var string
     *
     * @ORM\Column(name="status", type="string", length=50, nullable=false)
     */
    private $status = self::STATUS_PREPARE;

    /**
     * Фаза хода
     * @var string
     *
     * @ORM\Column(name="phase", type="string", length=50, nullable=false)
     */
    private $phase = self::PHASE_GET;

    /**
     * @ORM\OneToMany(targetEntity="CardIn", mappedBy="room", cascade={"all"}, fetch="EAGER")
     **/
    private $cardsIn;

    /** @var \SplObjectStorage */
    private $connections;

    /** ============================================================
     *                   DO NOT REMOVE THIS
     *  ============================================================ **/

    public function __construct()
    {
        $this->hash     = md5(uniqid(time(), true));
        $this->cardsIn  = new ArrayCollection();
        $this->players  = new ArrayCollection();
    }

    public function __toString()
    {
        return $this->hash;
    }

    /**
     * @param integer $id
     * @return boolean
     */
    public function isPlayer($id)
    {
        foreach($this->players as $player) {
            if($player->getId() === $id) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param integer $id
     * @return Player|null
     */
    public function getPlayerByUserId($id)
    {
        foreach($this->players as $player) {
            if($player->getUser()->getId() === (int)$id) {
                return $player;
            }
        }

        return null;
    }

    public function setCardToPlayer($player)
    {
        foreach($this->cardsIn as &$cardData) {
            if(!$cardData->isOnTable() && $cardData->getPlayer() === null) {
                $cardData->setPlayer($player);

                return $cardData;
            }
        }
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
     * Get username
     *
     * @return string
     */
    public function getHash()
    {
        return $this->hash;
    }

    /**
     * @return Deck
     */
    public function getDeck()
    {
        return $this->deck;
    }

    /**
     * Add player
     *
     * @param Player $player
     * @return Room
     */
    public function addPlayer($player)
    {
        $this->players->add($player);

        return $this;
    }

    /**
     * add player
     *
     * @param Player $player
     * @return Room
     */
    public function removePlayer($player)
    {
        $this->players->removeElement($player);

        return $this;
    }

    /**
     * Get players
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getPlayers()
    {
        return $this->players;
    }

    /**
     * Get status
     *
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set status
     * @param string $status
     *
     * @return Room
     */
    public function setStatus($status)
    {
        if (!in_array($status, [
            self::STATUS_PREPARE,
            self::STATUS_PLAY
        ])) {
            throw new \InvalidArgumentException("Invalid status");
        }

        $this->status = $status;

        return $this;
    }

    /**
     * @return Room
     */
    public function nextTurn()
    {
        if($this->turn === null) {
            $this->turn = $this->players->first();
        } else {
            // TODO: rewrite
            $key = $this->players->indexOf($this->turn);
            if(++$key >= $this->players->count()){
                $this->turn = $this->players[0];
            } else {
                $this->turn = $this->players[$key];
            }
        }

        return $this;
    }

    /**
     * @return Player
     */
    public function getTurn()
    {
        return $this->turn;
    }

    /**
     * @return string
     */
    public function getPhase()
    {
        return $this->phase;
    }

    /**
     * @param string $phase
     *
     * @return Room
     */
    public function setPhase($phase)
    {
        $this->phase = $phase;

        return $this;
    }

    /**
     * Add card
     *
     * @param CardIn $cardIn
     *
     * @return Room
     */
    public function addCardIn(CardIn $cardIn)
    {
        $this->cardsIn->add($cardIn);

        return $this;
    }

    /**
     * Remove cardIn
     *
     * @param CardIn $cardIn
     *
     * @return Room
     */
    public function removeCardIn(CardIn $cardIn)
    {
        $this->cardsIn->removeElement($cardIn);

        return $this;
    }

    /**
     * Set cardsIn
     *
     * @param array $cardsIn
     *
     * @return Room
     */
    public function setCardsIn(array $cardsIn)
    {
        $this->cardsIn = new ArrayCollection($cardsIn);

        return $this;
    }

    /**
     * Get cardsIn
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getCardsIn()
    {
        return $this->cardsIn;
    }

    public function attachConnect(ConnectionInterface $connect, $userId)
    {
        if ($this->connections === null) {
            $this->connections = new \SplObjectStorage;
        }
        $this->connections->attach($connect, $userId);
    }

    public function getConnectByUserId($userId)
    {
        if ($this->connections !== null) {
            foreach($this->connections as $connect) {
                if($this->connections->getInfo() === (int)$userId) {
                    return $connect;
                }
            }

            return null;
        }

        return null;
    }

    public function send($message)
    {
        if ($this->connections !== null) {
            foreach ($this->connections as $connect) {
                $connect->send($message);
            }
        }
    }

    public function detachConnect(ConnectionInterface $connect)
    {
        if ($this->connections != null) {
            $this->connections->detach($connect);
        }
    }
}