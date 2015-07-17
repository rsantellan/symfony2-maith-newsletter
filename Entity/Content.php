<?php

namespace Maith\NewsletterBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

use Gedmo\Mapping\Annotation as Gedmo;

/**
 * Content
 *
 * @ORM\Table(name="maith_newsletter_content")
 * @ORM\Entity
 */
class Content
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="title", type="string", length=255)
     */
    private $title;

    /**
     * @var string
     *
     * @ORM\Column(name="body", type="text", nullable=true)
     */
    private $body;

    /**
     * @var \DateTime
     *
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(name="createdat", type="datetime")
     */
    private $createdat;

    /**
     * @var \DateTime
     *
     * @Gedmo\Timestampable(on="update")
     * @ORM\Column(name="updatedat", type="datetime")
     */
    private $updatedat;

    /**
     * @var boolean
     *
     * @ORM\Column(name="active", type="boolean")
     */
    private $active = 1;


    /**
     * @ORM\OneToMany(targetEntity="ContentUser", mappedBy="content")
     */
    private $contentUser;
    
    /**
     * @ORM\OneToMany(targetEntity="ContentSend", mappedBy="content")
     * @ORM\OrderBy({"createdat" = "ASC"})
     */
    private $contentSend;
    
    
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
     * Set title
     *
     * @param string $title
     * @return Content
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get title
     *
     * @return string 
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set body
     *
     * @param string $body
     * @return Content
     */
    public function setBody($body)
    {
        $this->body = $body;

        return $this;
    }

    /**
     * Get body
     *
     * @return string 
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * Set createdat
     *
     * @param \DateTime $createdat
     * @return Content
     */
    public function setCreatedat($createdat)
    {
        $this->createdat = $createdat;

        return $this;
    }

    /**
     * Get createdat
     *
     * @return \DateTime 
     */
    public function getCreatedat()
    {
        return $this->createdat;
    }

    /**
     * Set updatedat
     *
     * @param \DateTime $updatedat
     * @return Content
     */
    public function setUpdatedat($updatedat)
    {
        $this->updatedat = $updatedat;

        return $this;
    }

    /**
     * Get updatedat
     *
     * @return \DateTime 
     */
    public function getUpdatedat()
    {
        return $this->updatedat;
    }

    /**
     * Set active
     *
     * @param boolean $active
     * @return Content
     */
    public function setActive($active)
    {
        $this->active = $active;

        return $this;
    }

    /**
     * Get active
     *
     * @return boolean 
     */
    public function getActive()
    {
        return $this->active;
    }
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->contentUser = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Add contentUser
     *
     * @param \Maith\NewsletterBundle\Entity\ContentUser $contentUser
     * @return Content
     */
    public function addContentUser(\Maith\NewsletterBundle\Entity\ContentUser $contentUser)
    {
        $this->contentUser[] = $contentUser;

        return $this;
    }

    /**
     * Remove contentUser
     *
     * @param \Maith\NewsletterBundle\Entity\ContentUser $contentUser
     */
    public function removeContentUser(\Maith\NewsletterBundle\Entity\ContentUser $contentUser)
    {
        $this->contentUser->removeElement($contentUser);
    }

    /**
     * Get contentUser
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getContentUser()
    {
        return $this->contentUser;
    }

    /**
     * Add contentSend
     *
     * @param \Maith\NewsletterBundle\Entity\ContentSend $contentSend
     * @return Content
     */
    public function addContentSend(\Maith\NewsletterBundle\Entity\ContentSend $contentSend)
    {
        $this->contentSend[] = $contentSend;

        return $this;
    }

    /**
     * Remove contentSend
     *
     * @param \Maith\NewsletterBundle\Entity\ContentSend $contentSend
     */
    public function removeContentSend(\Maith\NewsletterBundle\Entity\ContentSend $contentSend)
    {
        $this->contentSend->removeElement($contentSend);
    }

    /**
     * Get contentSend
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getContentSend()
    {
        return $this->contentSend;
    }
}
