<?php

namespace Maith\NewsletterBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

use Gedmo\Mapping\Annotation as Gedmo;

/**
 * ContentSend
 *
 * @ORM\Table(name="maith_newsletter_content_send")
 * @ORM\Entity
 */
class ContentSend
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
     * @ORM\Column(name="body", type="blob")
     */
    private $body;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="sendat", type="datetime")
     */
    private $sendat;
    
    /**
     * @var \DateTime
     *
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(name="createdat", type="datetime")
     */
    private $createdat;

    /**
     * @var boolean
     *
     * @ORM\Column(name="active", type="boolean")
     */
    private $active = true;

    /**
     * @var integer
     *
     * @ORM\Column(name="quantitySended", type="integer")
     */
    private $quantitySended = 0;


    /**
     * @var boolean
     *
     * @ORM\Column(name="sended", type="boolean")
     */
    private $sended = false;
    
    /**
     * @ORM\ManyToOne(targetEntity="Content", inversedBy="contentSend")
     * @ORM\JoinColumn(name="maith_newsletter_content_id", referencedColumnName="id")
     */
    private $content;
    
    /**
     * @ORM\OneToMany(targetEntity="ContentSendUser", mappedBy="content")
     */
    private $contentUser;    
    
    /**
     * @ORM\ManyToOne(targetEntity="EmailLayout", inversedBy="contentSend")
     * @ORM\JoinColumn(name="maith_newsletter_email_layout_id", referencedColumnName="id")
     */
    private $emailLayout;    
    
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
     * @return ContentSend
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
     * @return ContentSend
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
     * @return ContentSend
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
     * Set active
     *
     * @param boolean $active
     * @return ContentSend
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
     * Set quantitySended
     *
     * @param integer $quantitySended
     * @return ContentSend
     */
    public function setQuantitySended($quantitySended)
    {
        $this->quantitySended = $quantitySended;

        return $this;
    }

    /**
     * Get quantitySended
     *
     * @return integer 
     */
    public function getQuantitySended()
    {
        return $this->quantitySended;
    }
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->setSendat(new \DateTime());
        $this->contentUser = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Set content
     *
     * @param \Maith\NewsletterBundle\Entity\Content $content
     * @return ContentSend
     */
    public function setContent(\Maith\NewsletterBundle\Entity\Content $content = null)
    {
        $this->content = $content;

        return $this;
    }

    /**
     * Get content
     *
     * @return \Maith\NewsletterBundle\Entity\Content 
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * Add contentUser
     *
     * @param \Maith\NewsletterBundle\Entity\ContentSendUser $contentUser
     * @return ContentSend
     */
    public function addContentUser(\Maith\NewsletterBundle\Entity\ContentSendUser $contentUser)
    {
        $this->contentUser[] = $contentUser;

        return $this;
    }

    /**
     * Remove contentUser
     *
     * @param \Maith\NewsletterBundle\Entity\ContentSendUser $contentUser
     */
    public function removeContentUser(\Maith\NewsletterBundle\Entity\ContentSendUser $contentUser)
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
     * Set sended
     *
     * @param boolean $sended
     * @return ContentSend
     */
    public function setSended($sended)
    {
        $this->sended = $sended;

        return $this;
    }

    /**
     * Get sended
     *
     * @return boolean 
     */
    public function getSended()
    {
        return $this->sended;
    }

    /**
     * Set sendat
     *
     * @param \DateTime $sendat
     * @return ContentSend
     */
    public function setSendat($sendat)
    {
        $this->sendat = $sendat;

        return $this;
    }

    /**
     * Get sendat
     *
     * @return \DateTime 
     */
    public function getSendat()
    {
        return $this->sendat;
    }

    /**
     * Set emailLayout
     *
     * @param \Maith\NewsletterBundle\Entity\EmailLayout $emailLayout
     *
     * @return ContentSend
     */
    public function setEmailLayout(\Maith\NewsletterBundle\Entity\EmailLayout $emailLayout = null)
    {
        $this->emailLayout = $emailLayout;

        return $this;
    }

    /**
     * Get emailLayout
     *
     * @return \Maith\NewsletterBundle\Entity\EmailLayout
     */
    public function getEmailLayout()
    {
        return $this->emailLayout;
    }
}
