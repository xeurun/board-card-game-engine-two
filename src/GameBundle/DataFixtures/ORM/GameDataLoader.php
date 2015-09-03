<?php

namespace GameBundle\DataFixtures;

use GameBundle\Repository\CardRepository;
use GameBundle\Repository\ElementRepository;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\ORM\EntityManager;
use Doctrine\Common\Persistence\ObjectManager;

use GameBundle\Constants\Ingridients;
use GameBundle\Constants\CardType;
use GameBundle\Constants\Potion;
use GameBundle\Constants\Mascot;
use GameBundle\Constants\Powder;
use GameBundle\Constants\Creation;
use GameBundle\Constants\Curse;
use GameBundle\Constants\DeckName;

use GameBundle\Entity\Card;

class GameDataLoader implements FixtureInterface, ContainerAwareInterface
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
        /** @var EntityManager $em */
        $em = $this->container->get('doctrine.orm.entity_manager');
        /** @var ElementRepository $elementRepository */
        $elementRepository = $this->container->get('element.repository');
        /** @var CardRepository $cardRepository */
        $cardRepository = $this->container->get('card.repository');

        try {
        if(!is_dir($this->container->getParameter('kernel.root_dir') . '/../web/uploads')) {
            copy(__DIR__ . '/../assets/', $this->container->getParameter('kernel.root_dir') . '/../web');
        }

        $elements = [

        ];

        foreach ($elements as $code => $element) {
            $elementRepository->findOrCreate($code, $element[0], $element[1]);
        }

        $cards = [

        ];

        foreach($cards as $key => $data) {
            $card = new Card();
            $card->setName($key);
            $card->setType($data[0]);
            $card->setCaption($data[1]);
            $card->setPoints($data[2]);
            foreach($data[3] as $requirement) {
                $element = $elementRepository->findOrCreate($requirement);
                $card->addRequirement($em->getReference('GameBundle:Element', $element->getId()));
            }
            foreach($data[4] as $ingridient) {
                $element = $elementRepository->findOrCreate($ingridient);
                $card->addIngridient($em->getReference('GameBundle:Element', $element->getId()));
            }
            $card->setFile($data[5]);
            $card->setDeck($data[6]);
            $card->setCount($data[7]);

            $cardRepository->save($card);
        }
        } catch(\Exception $ex) {
            var_dump($ex->getMessage(). ' in file ' . $ex->getFile() . ' on line ' . $ex->getLine());
        }
    }
}