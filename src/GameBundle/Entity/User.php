<?php

namespace GameBundle\Entity;

use JsonSerializable;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * User
 *
 * @ORM\Table(options={"comment": "Пользователи системы"})
 * @ORM\Entity(repositoryClass="GameBundle\Repository\UserRepository")
 */
class User extends BaseEntity implements UserInterface, JsonSerializable
{
    const STATE_ACTIVE = false; // Аккаунт активен
    const STATE_DELETED = true; // Аккаунт удален

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * Уникальный логин пользователя
     * @var string
     *
     * @ORM\Column(name="username", type="string", length=20, unique=true, nullable=false)
     */
    private $username;

    /**
     * @var string
     *
     * @ORM\Column(name="password", type="string", length=128, nullable=true)
     */
    private $password;

    private $plainPassword;
    private $repeatPassword;

    /**
     * Email
     * @var string
     *
     * @ORM\Column(name="email", type="string", length=100, unique=true, nullable=false)
     */
    private $email;

    /**
     * Имя
     * @var string
     *
     * @ORM\Column(name="firstname", type="string", length=100, nullable=true)
     */
    private $firstname;

    /**
     * Фамилия
     * @var string
     *
     * @ORM\Column(name="lastname", type="string", length=100, nullable=true)
     */
    private $lastname;

    /**
     * Секретный ключ
     * @var string
     * @ORM\Column(name="secretKey", type="text", nullable=true)
     */
    private $secretKey;

    /**
     * Время генерации секретного ключа
     * @var \Datetime
     * @ORM\Column(name="secretKeyTime", type="datetime", nullable=true)
     */
    private $secretKeyTime;

    /**
     * Роль пользователя
     * @var string
     * @ORM\Column(name="role", type="string", length=32, nullable=false, options={"default": "ROLE_USER"})
     */
    private $role = 'ROLE_USER';

    /**
     * Состояние удаления пользователя
     *
     * @var string
     * @ORM\Column(name="deleted", type="boolean", nullable=true, options={"default": false})
     */
    private $deleted = false;

    /**
     * Дата создания
     * @var \DateTime
     * @ORM\Column(name="createAt", type="datetime", nullable=true)
     */
    private $createAt;

    /**
     * Дата обновления
     * @var \DateTime
     * @ORM\Column(name="updateAt", type="datetime", nullable=true)
     */
    private $updateAt;

    /**
     * @ORM\OneToMany(targetEntity="Player", mappedBy="user", cascade={"persist"})
     **/
    private $players;

    /** ============================================================
     *                   DO NOT REMOVE THIS
     *  ============================================================ **/

    public function __construct()
    {
    }

    public function __toString()
    {
        return $this->username;
    }

    public static function getAllRoles()
    {
        return array(
            'ROLE_USER',
            'ROLE_ADMIN',
        );
    }

    public function jsonSerialize()
    {
        return array(
            'id' => $this->id,
            'username' => $this->username
        );
    }

    /**
     * Get roles
     *
     * @return array
     */
    public function getRoles()
    {
        return [$this->role];
    }

    /**
     *
     * @param string $role
     * @return boolean
     */
    public function hasRole($role)
    {
        return (boolean) in_array($role, $this->getRoles());
    }

    /**
     * @inheritDoc
     */
    public function getSalt()
    {
        return null;
    }

    /**
     * @inheritDoc
     */
    public function eraseCredentials()
    {

    }

    /**
     * Генерация секретного ключа для формирования ссылки
     * @return string
     */
    public function generateSecretKey()
    {
        $userDependedValue = $this->getSecretKey();
        if (empty($userDependedValue)) {
            $userDependedValue = $this->getId();
        }

        $this->secretKey = $this->genereateUniqueHash($userDependedValue);
        $this->setSecretKeyTime(new \DateTime('now'));
    }

    /**
     * Генерирует уникальный хэш с использованием соли
     *
     * @param string $salt
     * @return string
     */
    private function genereateUniqueHash($salt)
    {
        return md5(time() . $salt . rand(0, 9999));
    }

    function getPlainPassword()
    {
        return $this->plainPassword;
    }

    function setPlainPassword($plainPassword)
    {
        $this->plainPassword = $plainPassword;
    }

    public function toArray()
    {
        return array(
            'id' => $this->getId(),
            'username' => $this->getUsername(),
            'email' => $this->getEmail(),
            'firstname' => $this->getFirstname(),
            'lastname' => $this->getLastname(),
            'createdAt' => $this->getCreateAt(),
            'updatedAt' => $this->getUpdateAt()
        );
    }

    public function getFullname()
    {
        return sprintf("%s %s", $this->getFirstname(), $this->getLastname());
    }

    /** ============================================================
     *  IF YOU WANT TO REGENERATE THE ENTITY, PLEASE CLEAR ALL BELOW
     *  ============================================================ **/

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set username
     *
     * @param string $username
     * @return User
     */
    public function setUsername($username)
    {
        $this->username = $username;

        return $this;
    }

    /**
     * Get username
     *
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * Set password
     *
     * @param string $password
     * @return User
     */
    public function setPassword($password)
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Get password
     *
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * Set email
     *
     * @param string $email
     * @return User
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get email
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set secretKey
     *
     * @param string $secretKey
     * @return User
     */
    public function setSecretKey($secretKey)
    {
        $this->secretKey = $secretKey;

        return $this;
    }

    /**
     * Get secretKey
     *
     * @return string
     */
    public function getSecretKey()
    {
        return $this->secretKey;
    }

    /**
     * Set secretKeyTime
     *
     * @param \DateTime $secretKeyTime
     * @return User
     */
    public function setSecretKeyTime($secretKeyTime)
    {
        $this->secretKeyTime = $secretKeyTime;

        return $this;
    }

    /**
     * Get secretKeyTime
     *
     * @return \DateTime
     */
    public function getSecretKeyTime()
    {
        return $this->secretKeyTime;
    }

    /**
     * Set role
     *
     * @param string $role
     * @return User
     */
    public function setRole($role)
    {
        $this->role = $role;

        return $this;
    }

    /**
     * Get role
     *
     * @return string
     */
    public function getRole()
    {
        return $this->role;
    }

    /**
     * @return string
     */
    public function getDeleted()
    {
        return $this->deleted;
    }

    /**
     * @param string $deleted
     */
    public function setDeleted($deleted)
    {
        $this->deleted = $deleted;
    }

    /**
     * @return mixed
     */
    public function getRepeatPassword()
    {
        return $this->repeatPassword;
    }

    /**
     * @param mixed $repeatPassword
     */
    public function setRepeatPassword($repeatPassword)
    {
        $this->repeatPassword = $repeatPassword;
    }

    /**
     * @return string
     */
    public function getFirstname()
    {
        return $this->firstname;
    }

    /**
     * @param string $firstname
     */
    public function setFirstname($firstname)
    {
        $this->firstname = $firstname;
    }

    /**
     * @return string
     */
    public function getLastname()
    {
        return $this->lastname;
    }

    /**
     * @param string $lastname
     */
    public function setLastname($lastname)
    {
        $this->lastname = $lastname;
    }

    public function updateUpdateAt()
    {
        $this->updateAt = new \DateTime('now');
    }

    public function updateCreateAt()
    {
        $this->createAt = new \DateTime('now');
    }

    /**
     * @param \DateTime $createAt
     */
    public function setCreateAt($createAt)
    {
        $this->createAt = $this->convertDate($createAt);
    }

    /**
     * @return \DateTime
     */
    public function getCreateAt()
    {
        return $this->createAt;
    }

    /**
     * @param mixed $updateAt
     */
    public function setUpdateAt($updateAt)
    {
        $this->updateAt = $this->convertDate($updateAt);
    }

    /**
     * @return mixed
     */
    public function getUpdateAt()
    {
        return $this->updateAt;
    }

    /**
     * Add player
     *
     * @param Player $player
     * @return User
     */
    public function addPlayer($player)
    {
        $this->players->add($player);

        return $this;
    }

    /**
     * add player
     *
     * @param Player $player
     * @return User
     */
    public function removePlayer($player)
    {
        $this->players->removeElement($player);

        return $this;
    }

    /**
     * Get players
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getPlayers()
    {
        return $this->players;
    }
}