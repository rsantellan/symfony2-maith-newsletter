<?php

namespace Maith\NewsletterBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ContentUser.
 *
 * @ORM\Table(name="maith_newsletter_content_send_user")
 * @ORM\Entity
 */
class ContentSendUser
{
    /**
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="ContentSend", inversedBy="contentUser")
     * @ORM\JoinColumn(name="maith_newsletter_content_send_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $content;

    /**
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="User", inversedBy="userSendContent")
     * @ORM\JoinColumn(name="maith_newsletter_user_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $user;

    /**
     * @var bool
     *
     * @ORM\Column(name="active", type="boolean")
     */
    private $active;

    /**
     * @var int
     * 
     * @ORM\Column(name="hits", type="integer", options={"default" = 0})
     */
    private $hits;

    /**
     * Set active.
     *
     * @param bool $active
     *
     * @return ContentUser
     */
    public function setActive($active)
    {
        $this->active = $active;

        return $this;
    }

    /**
     * Get active.
     *
     * @return bool
     */
    public function getActive()
    {
        return $this->active;
    }

    /**
     * Set content.
     *
     * @param \Maith\NewsletterBundle\Entity\Content $content
     *
     * @return ContentUser
     */
    public function setContent(\Maith\NewsletterBundle\Entity\Content $content)
    {
        $this->content = $content;

        return $this;
    }

    /**
     * Get content.
     *
     * @return \Maith\NewsletterBundle\Entity\Content
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * Set user.
     *
     * @param \Maith\NewsletterBundle\Entity\User $user
     *
     * @return ContentUser
     */
    public function setUser(\Maith\NewsletterBundle\Entity\User $user)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user.
     *
     * @return \Maith\NewsletterBundle\Entity\User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set hits.
     *
     * @param int $hits
     *
     * @return ContentSendUser
     */
    public function setHits($hits)
    {
        $this->hits = $hits;

        return $this;
    }

    /**
     * Get hits.
     *
     * @return int
     */
    public function getHits()
    {
        return $this->hits;
    }
}
