<?php

namespace GameBundle\Controller;

use GameBundle\Entity\CardInDeck;
use GameBundle\Entity\Deck;
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
 * @Route("/api")
 */
class ApiController extends BaseController
{
    /**
     * @Route("/send")
     * @METHOD("POST")
     */
    public function sendAction(Request $request)
    {
        /** ApplicationWebSocket applicationwebsocket */
        $applicationwebsocket = $this->get('application.websocket');
        $applicationwebsocket->onMessage(null, 'test');

        return new Response();
    }
}