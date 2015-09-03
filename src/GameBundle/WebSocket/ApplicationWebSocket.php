<?php

namespace GameBundle\WebSocket;

use Doctrine\Common\Collections\ArrayCollection;
use GameBundle\Constants\Ingridients;
use GameBundle\Entity\Card;
use GameBundle\Entity\CardIn;
use GameBundle\Entity\Element;
use GameBundle\Entity\Player;
use GameBundle\Repository\CardRepository;
use GameBundle\Repository\PlayerRepository;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;

use Doctrine\ORM\EntityManager;
use GameBundle\Entity\Room;
use GameBundle\Repository\RoomRepository;

class ApplicationWebSocket implements MessageComponentInterface
{
    /**
     * @var \SplObjectStorage
     */
    protected $connections;

    /**
     * @var Room[]
     */
    protected $rooms;

    /**
     * @var ContainerInterface
     */
    protected $container;

    /** @var RoomRepository */
    protected $roomRepository;

    /** @var CardInRepository */
    protected $cardInRepository;

    /** @var PlayerRepository */
    protected $playerRepository;

    /** @var EntityManager */
    protected $em;

    public function __construct(ContainerInterface $container) {
        $this->container = $container;
        $this->cardInRepository = $this->container->get('cardIn.repository');
        $this->roomRepository = $this->container->get('room.repository');
        $this->playerRepository = $this->container->get('player.repository');
        $this->em = $this->container->get('doctrine.orm.entity_manager');
        $this->connections = new \SplObjectStorage;
        $this->rooms = [];
    }

    public function onOpen(ConnectionInterface $connect) {
        var_dump(sprintf('Connect: %s', $connect->resourceId));
        $this->connections->attach($connect);
    }

    public function onMessage(ConnectionInterface $connect = null, $message) {
        var_dump(sprintf('Message: %s', $message));
        $this->em->beginTransaction();
        try {
            $message = json_decode($message);
            $roomId = $message->room;
            $userId = $message->user;

            if(!isset($this->rooms[$roomId])) {
                /** @var Room $room */
                $room = $this->roomRepository->find($roomId);
                $this->rooms[$roomId] = $room;
            } else {
                $room = $this->rooms[$roomId];
            }

            $result = [
                'event' => $message->event
            ];

            switch($message->event) {
                case 'auth':
                    $room->attachConnect($connect, $userId);
                    $this->connections->detach($connect);

                    $status = $room->getStatus();
                    $result['status'] = $status;
                    if($status !== Room::STATUS_PLAY) {
                        $result['players'] = [];
                        foreach($room->getPlayers() as $player) {
                            $result['players'][] = $player->getUser()->getUsername();
                        }
                    }

                    $player = $room->getPlayerByUserId($userId);
                    if($player !== null) {
                        $result['player'] = $player->getId();
                    }

                    $connect->send(json_encode($result));
                    var_dump(sprintf('Send: %s', json_encode($result)));
                    break;
                case 'request':
                    $result['action'] = $message->action;
                    $result['user'] = $userId;
                    switch($message->action) {
                        case 'request':
                            $creatorId = $room->getCreator()->getId();
                            $createrConnect = $room->getConnectByUserId($creatorId);
                            if($createrConnect !== null) {
                                $user = $this->em->getReference('GameBundle:User', $userId);
                                $createrConnect->send(json_encode(array_merge($result, [
                                    'username' => $user->getUsername()
                                ])));
                            }
                            $result['sended'] = $createrConnect !== null;
                            $connect->send(json_encode($result));
                            var_dump(sprintf('Send: %s', json_encode($result)));
                            break;
                        case 'allow':
                            $room->addPlayer(new Player($room, $this->em->getReference('GameBundle:User', $message->allowId)));
                            $this->roomRepository->save($room);
                            $player = $room->getPlayerByUserId($message->allowId);
                            if($player !== null) {
                                $result['player'] = $player->getId();
                                $result['username'] = $player->getUser()->getUsername();
                            }
                            var_dump(sprintf('Send: %s', json_encode($result)));
                            $room->send(json_encode($result));
                            break;
                        case 'deny':
                            $userConnect = $room->getConnectByUserId($message->denyId);
                            if($userConnect !== null) {
                                $userConnect->send(json_encode($result));
                                var_dump(sprintf('Send: %s', json_encode($result)));
                            } else {
                                var_dump('User connect not found');
                            }
                            break;
                    }
                    break;
                case 'game':
                    $result['action'] = $message->action;
                    switch($message->action) {
                        case 'start':
                            $room->setStatus(Room::STATUS_PLAY);
                            $room->nextTurn();
                            $this->roomRepository->save($room);
                            $cardsIn = $room->getCardsIn()->toArray();
                            shuffle($cardsIn);

                            $i = 0;
                            // карты на стол
                            for(; $i < Room::START_CARDS_ON_TABLE; $i++) {
                                /** @var CardIn $cardIn */
                                $cardIn = $cardsIn[$i];
                                $cardIn->setOnTable(true);
                                $this->cardInRepository->save($cardIn);
                            }

                            // выдаем карты игрокам
                            /** @var Player $player */
                            foreach($room->getPlayers() as $player) {
                                for(; $i < Room::START_CARDS_IN_HANDS; $i++) {
                                    /** @var CardIn $cardIn */
                                    $cardIn = $cardsIn[$i];
                                    $cardIn->setPlayer($player);
                                    $this->cardInRepository->save($cardIn);
                                    $player->addRoomCard($cardIn);
                                }
                            }

                            $this->getGameInfo($room, $cardsIn, $result);

                            var_dump(sprintf('Send: %s', json_encode($result)));
                            $room->send(json_encode($result));
                            break;
                        case 'info':
                            $result['status'] = $room->getStatus();
                            $result['phase'] = $room->getPhase();
                            $cardsIn = $room->getCardsIn()->toArray();

                            $this->getGameInfo($room, $cardsIn, $result);

                            var_dump(sprintf('Send: %s', json_encode($result)));
                            $connect->send(json_encode($result));
                            break;
                        case 'card':
                            $player = $this->em->getReference('GameBundle:Player', $message->playerId);
                            $cardsIn = $room->getCardsIn()->toArray();
                            $cardIn = $this->getCardFromDeck($cardsIn);
                            $cardIn->setPlayer($player);
                            $this->cardInRepository->save($cardIn);
                            $room->setPhase(Room::PHASE_TURN);
                            $this->roomRepository->save($room);
                            $player->addRoomCard($cardIn);
                            $result['card'] = $cardIn;
                            $result['phase'] = $room->getPhase();
                            $result['player'] = $player->getId();

                            var_dump(sprintf('Send: %s', json_encode($result)));
                            $room->send(json_encode($result));
                            break;
                        case 'throw':
                            $player = $room->getPlayerByUserId($userId);
                            // Проверяем что карта есть у пользователя
                            $userCardIn = $player->getRoomCard($message->card);
                            if ($userCardIn !== null) {
                                $cardsIn = $room->getCardsIn()->toArray();
                                $ingridients = [];
                                /** @var CardIn $cardIn */
                                foreach($cardsIn as $cardIn) {
                                    if($cardIn->isOnTable()) {
                                        /** @var Element $ingridient */
                                        foreach ($cardIn->getCard()->getIngridients() as $ingridient) {
                                            $ingridients[] = $ingridient->getCode();
                                        }
                                    }
                                }
                                $userCardIn->setOnTable(true);
                                $userCardIn->setPlayer(null);
                                $this->cardInRepository->save($userCardIn);
                                $points = 0;
                                foreach($userCardIn->getCard()->getIngridients() as $ingridient) {
                                    if(!isset($ingridients[$ingridient->getCode()])) {
                                        $points++;
                                    }
                                }
                                $player->addPoints($points);
                                $this->playerRepository->save($player);
                                $room->setPhase(Room::PHASE_GET);
                                $room->nextTurn();
                                $this->roomRepository->save($room);
                                $result['card'] = $userCardIn;
                                $result['phase'] = $room->getPhase();
                                $result['turn'] = $room->getTurn();
                                $result['player'] = $player->getId();
                                $result['points'] = $player->getPoints();
                                $player->removeRoomCard($message->card);
                                // шлем всем новую карту на столе
                                var_dump(sprintf('Send: %s', json_encode($result)));
                                $room->send(json_encode($result));
                            } else {
                                var_dump(sprintf('Player: %s, card not found', $player->getId()));
                            }
                            break;
                        case 'submit':
                            $player = $room->getPlayerByUserId($userId);
                            // Проверяем что главная карта есть у пользователя
                            $parentCard = $player->getRoomCard($message->card);
                            if ($parentCard !== null) {
                                $fail = false;
                                $childCards = [];
                                $ingridients = [];
                                $indx = 0;
                                // Магемы сохраняем в skip позже ненайденные пропустим
                                $skip = 0;
                                // Проверяем что нужные карты есть у пользователя
                                // Сохраняем ингридиенты
                                foreach($message->cards as $cardInId) {
                                    $childCard = $player->getRoomCard($cardInId);
                                    // Если нет у пользователя ищем на столе
                                    if($childCard === null) {
                                        $cardsIn = $room->getCardsIn()->toArray();
                                        /** @var CardIn $cardIn */
                                        foreach($cardsIn as $cardIn) {
                                            if($cardIn->isOnTable() && $cardIn->getId() === (int)$cardInId) {
                                                $childCard = $cardIn;
                                                break;
                                            }
                                        }
                                    }
                                    if ($childCard === null) {
                                        $fail = true;
                                        break;
                                    } else {
                                        $card = $childCard->getCard();
                                        // Если рецепт то добавляем его тип как ингридиент
                                        if($childCard->isRecipe()) {
                                            $ingridients[$indx++] = $card->getType();
                                        } else {
                                            /** @var Element $ingridient */
                                            foreach($card->getIngridients() as $ingridient) {
                                                $code = $ingridient->getCode();
                                                if($code === Ingridients::ELEMENTARY_MAGHEMITE) {
                                                    $skip++;
                                                } else {
                                                    $ingridients[$indx++] = $code;
                                                }
                                            }
                                        }
                                        $childCards[] = $childCard;
                                    }
                                }
                                if(!$fail) {
                                    $fail = 0;

                                    /** @var Element $requirement */
                                    foreach($parentCard->getCard()->getRequirements() as $requirement) {
                                        $search = array_search($requirement->getCode(), $ingridients);
                                        if($search === false) {
                                            $fail++;
                                        } else {
                                            unset($ingridients[$search]);
                                        }
                                    }

                                    if(!$fail || $skip >= $fail) {
                                        $parentCard->setRecipe(true);
                                        $this->cardInRepository->save($parentCard);
                                        /** @var CardIn $childCard */
                                        foreach($childCards as $childCard) {
                                            if($childCard->isOnTable()) {
                                                $childCard->setOnTable(false);
                                            }
                                            $childCard->setParent($parentCard);
                                            $childCard->setPlayer($player);
                                            $player->addRoomCard($childCard);
                                            $this->cardInRepository->save($childCard);
                                        }
                                        $points = $parentCard->getCard()->getPoints();
                                        $player->addPoints($points);
                                        $this->playerRepository->save($player);
                                        $result['points'] = $player->getPoints();
                                        $result['recipe'] = true;
                                        $result['card'] = $message->card;
                                        $result['cards'] = $message->cards;
                                        $result['player'] = $player->getId();
                                        // TODO: Удялать у пользователя главный и все компоненты
                                        $room->setPhase(Room::PHASE_GET);
                                        $room->nextTurn();
                                        $this->roomRepository->save($room);
                                    } else {
                                        var_dump(sprintf('Player: %s, lacking ingredients', $player->getId()));
                                        $result['recipe'] = false;
                                        $result['player'] = $player->getId();
                                    }
                                } else {
                                    var_dump(sprintf('Player: %s, child card not found', $player->getId()));
                                    $result['recipe'] = false;
                                    $result['player'] = $player->getId();
                                }
                                var_dump(sprintf('Send: %s', json_encode($result)));
                                $room->send(json_encode($result));
                            } else {
                                var_dump(sprintf('Player: %s, parent card not found', $player->getId()));
                            }
                            break;
                    }
                    break;
            }

            $this->em->commit();
        } catch(\Exception $ex) {
            $this->em->rollback();
            var_dump($ex->getMessage() . ' in ' . $ex->getFile() . ' on '. $ex->getLine());
        }
    }

    public function onClose(ConnectionInterface $connect) {
        var_dump(sprintf('Close: %s', $connect->resourceId));
        foreach($this->rooms as $room) {
            $room->detachConnect($connect);
        }
        $this->connections->detach($connect);
    }

    public function onError(ConnectionInterface $connect, \Exception $e) {
        var_dump(sprintf('Error: %s', $e->getMessage()));
        $connect->close();
    }

    /**
     * @param Room $room
     * @param array $cardsIn
     * @param array $result
     */
    private function getGameInfo($room, $cardsIn, &$result)
    {
        $result['turn'] = $room->getTurn();
        $result['phase'] = $room->getPhase();
        $result['onTable'] = [];
        $result['deck'] = [];
        $result['players'] = [];

        foreach($room->getPlayers() as $player) {
            $result['players'][$player->getId()] = $player;
        }

        /** @var CardIn $cardIn */
        foreach($cardsIn as $cardIn) {
            if($cardIn->isOnTable()) {
                $result['onTable'][$cardIn->getId()] = $cardIn;
                continue;
            }

            if(($player = $cardIn->getPlayer()) instanceof Player) {
                $playerId = $player->getId();

                if(isset($result['players'][$playerId])) {
                    $result['players'][$playerId]->addRoomCard($cardIn);
                }

                continue;
            }

            $result['deck'][$cardIn->getId()] = $cardIn;
        }
    }

    /**
     * @param array $cardsIn
     * @return CardIn|null
     */
    private function getCardFromDeck($cardsIn)
    {
        /** @var CardIn $cardIn */
        foreach($cardsIn as $cardIn) {
            if($cardIn->isOnTable()) {
                continue;
            }

            if($cardIn->getPlayer() instanceof Player) {
                continue;
            }

            return $cardIn;
        }

        return null;
    }
}