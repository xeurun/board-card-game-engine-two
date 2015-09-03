<?php

namespace GameBundle\Repository;

use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\UserInterface;

use GameBundle\Entity\User;

/**
 * UserRepository
 */
class UserRepository extends BaseRepository implements UserProviderInterface
{
    /**
     * @param string $username
     * @return null|User
     */
    public function loadUserByUsername($username)
    {
        $q = $this
            ->createQueryBuilder('u')
            ->where('u.username = :username')
            ->setParameter('username', $username)
            ->getQuery();

        return $q->getOneOrNullResult();
    }

    /**
     * @param $secretKey
     * @return mixed
     * @throws \Exception
     */
    public function getUserBySecretKey($secretKey)
    {

        $query = $this->createQueryBuilder('u');
        $query->select('u');
        $query->where('u.secretKey = :secretKey')->setParameter('secretKey', $secretKey);

        $fromDate = new \DateTime('-1 hour');
        $fromDate = $fromDate->format('Y-m-d H:i:s');

        $query->andWhere('u.secretKeyTime >= :fromDate')->setParameter('fromDate', $fromDate);

        return $query->getQuery()->getOneOrNullResult();
    }

    /**
     * @param UserInterface $user
     * @return null|object
     */
    public function refreshUser(UserInterface $user)
    {
        $class = get_class($user);
        if (!$this->supportsClass($class)) {
            throw new UnsupportedUserException(
                sprintf(
                    'Instances of "%s" are not supported.',
                    $class
                )
            );
        }
        if ($this->getEnvironment() == 'test') {
            return $user;
        }

        return $this->find($user->getId());
    }

    /**
     * @param type $class
     * @return boolean
     */
    public function supportsClass($class)
    {
        return $this->getEntityName() === $class
        || is_subclass_of($class, $this->getEntityName());
    }

    /**
     * Генерация секретного ключа
     * @param User $user
     */
    public function generateSecretKey(User $user)
    {
        $user->generateSecretKey();
        $this->getEntityManager()->flush();
    }

    /**
     * Удаление сущности
     * @param $entity
     * @throws \Doctrine\ORM\ORMException
     */
    public function remove($entity)
    {
        $entity->setDeleted(User::STATE_DELETED);
        $this->save($entity);
    }

    /**
     * Получить активного пользователя по id
     * @param $id
     * @return null|object
     */
    public function getById($id)
    {
        $user = $this->find($id);
        if ($user && !$user->getDeleted()) {
            return $user;
        }
    }

    /**
     * Создание пользователя
     * @param User $user
     * @throws \Exception
     */
    public function createUser(User $user)
    {
        try {
            $this->save($user);
        } catch (\Exception $ex){
            throw $ex;
        }
    }
}