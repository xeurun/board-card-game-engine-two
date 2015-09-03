<?php

namespace GameBundle\Helpers;

use GameBundle\Entity\User;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\AnonymousToken;

/**
 * Вспомогательный класс для пользователя
 */
class UserHelper
{
    /**
     * @var \Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface
     */
    private $encoderFactory;

    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;

    function __construct(EncoderFactoryInterface $encoderFactory, TokenStorageInterface $tokenStorage)
    {
        $this->encoderFactory = $encoderFactory;
        $this->tokenStorage = $tokenStorage;
    }

    public function clearTokenStorage()
    {
        $this->tokenStorage->setToken(new AnonymousToken('', $this->getUser()));
    }

    /**
     * Get the logged in user or null.
     * @return User
     */
    public function getUser()
    {
        if (null === $token = $this->tokenStorage->getToken()) {
            return;
        }

        if (!is_object($user = $token->getUser())) {
            return;
        }

        return $user;
    }

    /**
     * Обновление пароля
     * @param User $user
     */
    public function updatePassword(User $user)
    {
        $encoder = $this->encoderFactory->getEncoder($user);
        $password = $encoder->encodePassword($user->getPlainPassword(), null);
        $user->setPassword($password);
    }

    /**
     * Сверка пароля
     */
    public function checkPassword(User $user, $plainPassword)
    {
        $encoder = $this->encoderFactory->getEncoder($user);
        $encPassword = $encoder->encodePassword($plainPassword, null);

        return ($encPassword == $user->getPassword());
    }

    /**
     * Генерация пароля из 8 символов
     * @return string
     */
    public function passwordGenerator()
    {
        $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";

        return substr(str_shuffle($chars), 0, 8);
    }
}