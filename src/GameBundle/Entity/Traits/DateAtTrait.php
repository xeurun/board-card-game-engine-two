<?php

namespace GameBundle\Entity\Traits;

trait DateAtTrait
{
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
        $this->createAt = $createAt;
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
        $this->updateAt = $updateAt;
    }

    /**
     * @return mixed
     */
    public function getUpdateAt()
    {
        return $this->updateAt;
    }

    public function hasDateAtTrait() {
        return true;
    }
}