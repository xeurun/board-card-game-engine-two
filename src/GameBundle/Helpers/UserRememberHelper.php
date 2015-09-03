<?php

namespace GameBundle\Helpers;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Http\RememberMe\TokenBasedRememberMeServices;

use Symfony\Bridge\Doctrine\Security\User\EntityUserProvider;

class UserRememberHelper
{
    protected $container;
    protected $rememberMeService;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;

        $providerKey = 'main'; // defined in security.yml
        $securityKey = $this->container->getParameter('secret'); // defined in security.yml

        $userProvider = new EntityUserProvider($this->container->get('doctrine'), 'GameBundle\Entity\User');

        $this->rememberMeService = new TokenBasedRememberMeServices(
            array($userProvider),
            $securityKey,
            $providerKey,
            array(
                'name' => 'remember_me',
                'path' => '/',
                'domain' => null,
                'secure' => false,
                'httponly' => true,
                'lifetime' => $this->container->getParameter('remember_me_time'),
                'always_remember_me' => false,
                'remember_me_parameter' => '_remember_me'
            )
        );
    }

    public function rememberUser($token, $request, $response)
    {
        $this->rememberMeService->loginSuccess($request, $response, $token);
    }
}