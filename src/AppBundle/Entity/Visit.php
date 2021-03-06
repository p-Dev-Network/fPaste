<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class Visit
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
     * @ORM\Column(type="datetime", nullable=false)
     *
     * @var \DateTime
     */
    protected $date;

    /**
     * @ORM\Column(type="string", nullable=true)
     *
     * @var string
     */
    protected $IP;

    /**
     * @ORM\ManyToOne(targetEntity="Paste", inversedBy="visit")
     *
     * @var Paste
     */
    protected $paste;

    /**
     * @ORM\ManyToOne(targetEntity="User", inversedBy="visit")
     *
     * @var User
     */
    protected $user;

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

    /**
     * @return Paste
     */
    public function getPaste()
    {
        return $this->paste;
    }

    /**
     * @param Paste $paste
     */
    public function setPaste($paste)
    {
        $this->paste = $paste;
    }

    /**
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param User $user
     */
    public function setUser($user)
    {
        $this->user = $user;
    }

}