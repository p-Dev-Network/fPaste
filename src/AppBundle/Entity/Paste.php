<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class Paste
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue
     *
     * @var integer
     */
    protected $id;

    /**
     * @ORM\Column(type="string", nullable=false)
     *
     * @var string
     */
    protected $title;

    /**
     * @ORM\Column(type="text", nullable=false)
     *
     * @var string
     */
    protected $content;

    /**
     * @ORM\Column(type="datetime", nullable=false)
     *
     * @var \DateTime
     */
    protected $date;

    /**
     * @ORM\Column(type="boolean", nullable=false)
     *
     * @var boolean
     */
    protected $isActive = true;

    /**
     * @ORM\Column(type="boolean", nullable=false)
     *
     * @var boolean
     */
    protected $isDeletedByUser = false;

    /**
     * @ORM\Column(type="boolean", nullable=false)
     *
     * @var boolean
     */
    protected $isDeletedByAdmin = false;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     *
     * @var \DateTime
     */
    protected $deleteDate;

    /**
     * @ORM\Column(type="string", nullable=true)
     *
     * @var string
     */
    protected $IP;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param string $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * @param string $content
     */
    public function setContent($content)
    {
        $this->content = $content;
    }

    /**
     * @return \DateTime
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * @param \DateTime $date
     */
    public function setDate($date)
    {
        $this->date = $date;
    }

    /**
     * @return bool
     */
    public function isActive()
    {
        return $this->isActive;
    }

    /**
     * @param bool $isActive
     */
    public function setIsActive($isActive)
    {
        $this->isActive = $isActive;
    }

    /**
     * @return bool
     */
    public function isDeletedByUser()
    {
        return $this->isDeletedByUser;
    }

    /**
     * @param bool $isDeletedByUser
     */
    public function setIsDeletedByUser($isDeletedByUser)
    {
        $this->isDeletedByUser = $isDeletedByUser;
    }

    /**
     * @return bool
     */
    public function isDeletedByAdmin()
    {
        return $this->isDeletedByAdmin;
    }

    /**
     * @param bool $isDeletedByAdmin
     */
    public function setIsDeletedByAdmin($isDeletedByAdmin)
    {
        $this->isDeletedByAdmin = $isDeletedByAdmin;
    }

    /**
     * @return \DateTime
     */
    public function getDeleteDate()
    {
        return $this->deleteDate;
    }

    /**
     * @param \DateTime $deleteDate
     */
    public function setDeleteDate($deleteDate)
    {
        $this->deleteDate = $deleteDate;
    }

    /**
     * @return string
     */
    public function getIP()
    {
        return $this->IP;
    }

    /**
     * @param string $IP
     */
    public function setIP($IP)
    {
        $this->IP = $IP;
    }

}