<?php

namespace GameBundle\Controller;

use GameBundle\Entity\CardIn;
use GameBundle\Entity\Player;
use GameBundle\Exception\MessageException;
use GameBundle\Repository\PlayerRepository;
use GameBundle\Repository\RoomRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

use GameBundle\WebSocket\ApplicationWebSocket;
use GameBundle\Entity\Room;
use GameBundle\Repository\CardRepository;
use GameBundle\Constants\DeckName;

/**
 * @Route("/room")
 */
class RoomController extends BaseController
{

    /**
     * @Route("/debug/{hash}", requirements={"hash": "^(?!.*(new|debug)$).*"})
     * @ParamConverter("room", class="GameBundle:Room", options={"hash" = "hash"})
     * @Template()
     */
    public function debugAction(Request $request, Room $room)
    {
        return [
            'room' => $room
        ];
    }

    /**
     * @Route("/new")
     * @Method("POST")
     */
    public function newAction()
    {
        $response = new JsonResponse();

        /** @var RoomRepository $roomRepository */
        $roomRepository = $this->getRepository('room');
        /** @var PlayerRepository $playerRepository */
        $playerRepository = $this->getRepository('player');
        /** @var CardRepository $cardRepository */
        $cardRepository = $this->getRepository('card');

        try {
            $room = new Room();
            $roomRepository->save($room);

            $player = new Player($room, $this->getUser());
            $playerRepository->save($player);
            $room->addPlayer($player);

            $cards = $cardRepository->findBy(['deck' =>  DeckName::PRACTICLE]);
            foreach($cards as $card) {
                for($i = 0, $count = $card->getCount(); $i < $count; $i++) {
                    $cardIn = new CardIn($card, $room);
                    $cardRepository->save($cardIn);
                    $room->addCardIn($cardIn);
                }
            }

            $roomRepository->save($room);
        } catch(\Exception $ex) {
            throw new MessageException($ex->getMessage());
        }

        $response->setData([
            'hash' => $room->getHash()
        ]);

        return $response;
    }

    /**
     * @Route("/{hash}", requirements={"hash": "^(?!.*(new|debug)$).*"})
     * @ParamConverter("room", class="GameBundle:Room", options={"hash" = "hash"})
     * @Template()
     */
    public function enterAction(Request $request, Room $room)
    {
        return [
            'room'      => $room,
            'player'    => $room->getPlayerByUserId($this->getUser()->getId()),
            'ip'        => $this->container->getParameter('web_socket_server_ip')
        ];
    }
}