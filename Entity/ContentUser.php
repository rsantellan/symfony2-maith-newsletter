<?php

namespace Maith\NewsletterBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ContentUser.
 *
 * @ORM\Table(name="maith_newsletter_content_user")
 * @ORM\Entity
 */
class ContentUser
{
    /**
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="Content", inversedBy="contentUser")
     * @ORM\JoinColumn(name="maith_newsletter_content_id", referencedColumnName="id")
     */
    private $content;

    /**
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="User", inversedBy="contentUser")
     * @ORM\JoinColumn(name="maith_newsletter_user_id", referencedColumnName="id")
     */
    private $user;

    /**
     * @var bool
     *
     * @ORM\Column(name="active", type="boolean")
     */
    private $active;

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
}
