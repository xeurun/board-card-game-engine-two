<?php

namespace GameBundle\Repository;

use GameBundle\Entity\User;
use Symfony\Component\Translation\Translator;
use Symfony\Component\DependencyInjection\Container;

use Doctrine\ORM as ORM;

/**
 * BaseRepository
 */
abstract class BaseRepository extends ORM\EntityRepository
{
    /** @var Container */
    private $container;

    /** @var Translator */
    private $translator;

    /**
     * Внедряем все зависимости из контейнера
     * @param Container $container
     */
    public function setContainer(Container $container)
    {
        $this->container = $container;
        $this->translator = $this->container->get('translator');
    }

    /** Событие до вставки сущности */
    protected function prePersist($entity) {
        if($entity->hasDateAtTrait()) {
            $entity->updateCreateAt();
        }
        if($entity->hasCreatorTrait()) {
            $entity->setCreator($this->getCurrentUser());
        }
    }
    /** Событие после вставки сущности */
    protected function postPersist($entity) {}

    /** Событие до обновления сущности */
    protected function preUpdate($entity) {
        if($entity->hasDateAtTrait()) {
            $entity->updateUpdateAt();
        }
    }

    protected function getEnvironment()
    {
        return $this->container->getParameter('kernel.environment');
    }

    /**
     * Сохранение сущности
     * @param $entity
     * @throws \Doctrine\ORM\ORMException
     */
    public function save($entity)
    {
        try {
            $em = $this->getEntityManager();
            $isNew = is_null($entity->getId());

            if ($isNew) {
                $this->prePersist($entity);
                $em->persist($entity);
            } else {
                $this->preUpdate($entity);
            }

            $em->flush();

            if ($isNew) {
                $this->postPersist($entity);
            }

            $this->refresh($entity);
        } catch (\Exception $ex) {
            throw new ORM\ORMException(sprintf('Не удается сохранить объект %s, т.к %s', get_class($entity), $ex->getMessage()), 0, $ex);
        }
    }

    /**
     * Удаление сущности
     * @param $entity
     * @throws \Doctrine\ORM\ORMException
     */
    public function remove($entity)
    {
        try {
            $em = $this->getEntityManager();
            $em->remove($entity);
            $em->flush();
        } catch (\Exception $e) {
            throw new ORM\ORMException('Не удается удалить объект ' . get_class($entity), 0, $e);
        }
    }

    /**
     *
     */
    public function refresh($entity)
    {
        $this->getEntityManager()->refresh($entity);
    }

    /**
     *
     */
    public function clear()
    {
        $this->getEntityManager()->clear();
    }

    /**
     * @return User||null
     */
    public function getCurrentUser()
    {
        if ($token = $this->container->get('security.token_storage')->getToken()) {
            if ($token->getUser() instanceof User) {
                return $token->getUser();
            }
        }
    }
}