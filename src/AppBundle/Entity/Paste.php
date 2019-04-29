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
     * @ORM\Column(type="string", nullable=false)
     *
     * @var string
     */
    protected $privacy;

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
     * @ORM\Column(type="string", nullable=false)
     *
     * @var string
     */
    protected $url;

    /**
     * @ORM\Column(type="boolean", nullable=false)
     *
     * @var boolean
     */
    protected $isAnonymous;

    /**
     * @ORM\ManyToOne(targetEntity="User", inversedBy="paste")
     *
     * @var User
     */
    protected $user;

    /**
     * @ORM\OneToMany(targetEntity="Visit", mappedBy="paste")
     *
     * @var Visit
     */
    protected $visit;

    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Report", mappedBy="paste")
     *
     * @var Report
     */
    protected $report;

    /**
     * @ORM\Column(type="string", nullable=true)
     *
     * @var string
     */
    protected $name_hnp99;

    /**
     * @return string
     */
    public function getNameHnp99()
    {
        return $this->name_hnp99;
    }

    /**
     * @param string $name_hnp99
     */
    public function setNameHnp99($name_hnp99)
    {
        $this->name_hnp99 = $name_hnp99;
    }

    /**
     * @ORM\Column(type="boolean", nullable=false)
     *
     * @var boolean
     */
    protected $sendAsAdmin = false;

    /**
     * @return bool
     */
    public function isSendAsAdmin()
    {
        return $this->sendAsAdmin;
    }

    /**
     * @param bool $sendAsAdmin
     */
    public function setSendAsAdmin($sendAsAdmin)
    {
        $this->sendAsAdmin = $sendAsAdmin;
    }

    /**
     * @return Report
     */
    public function getReport()
    {
        return $this->report;
    }

    /**
     * @param Report $report
     */
    public function setReport($report)
    {
        $this->report = $report;
    }

    /**
     * @return Visit
     */
    public function getVisit()
    {
        return $this->visit;
    }

    /**
     * @param Visit $visit
     */
    public function setVisit($visit)
    {
        $this->visit = $visit;
    }

    /**
     * @return bool
     */
    public function isAnonymous()
    {
        return $this->isAnonymous;
    }

    /**
     * @param bool $isAnonymous
     */
    public function setIsAnonymous($isAnonymous)
    {
        $this->isAnonymous = $isAnonymous;
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

    /**
     * @return string
     */
    public function getPrivacy()
    {
        return $this->privacy;
    }

    /**
     * @param string $privacy
     */
    public function setPrivacy($privacy)
    {
        $this->privacy = $privacy;
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @param string $url
     */
    public function setUrl($url)
    {
        $this->url = $url;
    }

}