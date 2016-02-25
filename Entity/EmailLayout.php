<?php

namespace Maith\NewsletterBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

use Gedmo\Mapping\Annotation as Gedmo;
/**
 * Description of EmailLayout
 *
 * @author Rodrigo Santellan
 * @ORM\Table(name="maith_newsletter_email_layout")
 * @ORM\Entity
 */
class EmailLayout {
    
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
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="body", type="blob")
     */
    private $body;
    
    /**
     * @ORM\OneToMany(targetEntity="ContentSend", mappedBy="emailLayout")
     */
    private $contentSend;
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->contentSend = new \Doctrine\Common\Collections\ArrayCollection();
    }

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
     * Set name
     *
     * @param string $name
     *
     * @return EmailLayout
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set body
     *
     * @param string $body
     *
     * @return EmailLayout
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
     * Add contentSend
     *
     * @param \Maith\NewsletterBundle\Entity\ContentSend $contentSend
     *
     * @return EmailLayout
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
    
    public function __toString() {
        return $this->getName();
    }

}
