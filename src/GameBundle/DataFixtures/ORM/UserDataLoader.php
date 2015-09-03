<?php

namespace GameBundle\DataFixtures;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use GameBundle\Entity\User;

class UserDataLoader implements FixtureInterface, ContainerAwareInterface
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * {@inheritDoc}
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
    * {@inheritDoc}
    */
    public function load(ObjectManager $manager)
    {
        $userRepository = $this->container->get('user.repository');

        $user = new User();
        $user->setUsername("admin");
        $user->setPassword("JVX+VgHwOwQQwqqHyMCjxLJ2K4ZiGEpim1QdV2x6gWsAlkSbR4myjw==");
        $user->setEmail("admin@admin.admin");
        $user->setRole("ROLE_USER");

        $userRepository->save($user);

        $user = new User();
        $user->setUsername("test");
        $user->setPassword("JVX+VgHwOwQQwqqHyMCjxLJ2K4ZiGEpim1QdV2x6gWsAlkSbR4myjw==");
        $user->setEmail("test@test.test");
        $user->setRole("ROLE_USER");

        $userRepository->save($user);
    }
}