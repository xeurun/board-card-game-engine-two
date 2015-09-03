<?php

namespace GameBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

abstract class BaseController extends Controller
{
    protected function getEnvironment()
    {
        return $this->container->getParameter('kernel.environment');
    }

    /**
     * Получение репозитория
     * @param $entityName
     * @param string $bundleName
     * @return \Doctrine\Common\Persistence\ObjectRepository|object|string
     */
    protected function getRepository($entityName, $bundleName = 'GameBundle')
    {
        $entityName = lcfirst($entityName);
        $repository = sprintf('%s.repository', $entityName);

        if ($this->container->has($repository)) {
            return $this->container->get($repository);
        }

        $class = ucfirst($entityName);
        $repository = (class_exists($class))
            ? $this->getDoctrine()->getManager()->getRepository($class)
            : $this->getDoctrine()->getManager()->getRepository($bundleName .':'. $class);
        $repository->setContainer($this->container);

        return $repository;
    }

    /**
     * Получение формы из контейнера
     * @param string $name
     * @return AbstractType
     */
    protected function getForm($name) {
        $entityName = lcfirst($name);
        $form = sprintf('%s.form', $entityName);
        if (! $this->has($form)) {
            throw new \Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException($form);
        }

        return $this->get($form);
    }

    /**
     * Возвращает перевод
     * @param string $text
     * @param array $parameters
     * @return string
     */
    protected function trans($text, array $parameters = array())
    {
        return $this->get('translator')->trans($text, $parameters);
    }

    /**
     * Флеш сообщения 3х типов
     * @param $msg
     * @param string $type
     */
    protected function flashMessage($msg, $type='success')
    {
        if (!in_array($type, array('success', 'notice', 'error'))) {
            $type = 'success';
        }
        $this->get('session')->getFlashBag()->add($type, $this->trans($msg));
    }
}