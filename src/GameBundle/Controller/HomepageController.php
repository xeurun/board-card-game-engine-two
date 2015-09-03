<?php

namespace GameBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class HomepageController extends BaseController
{
    /**
     * @Route("/", name="homepage")
     * @Template()
     */
    public function homepageAction(Request $request)
    {
        return array();
    }

    /**
     * @Route("/debug")
     * @Template()
     */
    public function debugAction(Request $request)
    {
        $cardRepository = $this->getRepository('card');

        return [
            'cards' => $cardRepository->findAll()
        ];
    }
}