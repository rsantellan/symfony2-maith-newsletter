<?php

namespace Maith\NewsletterBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * User.
 *
 * @ORM\Table(name="maith_newsletter_user")
 * @ORM\Entity
 */
class User
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     * @Assert\Email()
     * @Assert\NotBlank()
     * @ORM\Column(name="email", type="string", length=255, unique=true)
     */
    private $email;

    /**
     * @var bool
     *
     * @ORM\Column(name="active", type="boolean")
     */
    private $active = true;

    /**
     * @ORM\ManyToMany(targetEntity="UserGroup", indexBy="name", inversedBy="users")
     * @ORM\JoinTable(name="maith_newsletter_users_groups")
     */
    protected $user_groups;

    /**
     * @ORM\OneToMany(targetEntity="ContentUser", mappedBy="user")
     */
    private $contentUser;

    /**
     * @ORM\OneToMany(targetEntity="ContentSendUser", mappedBy="user")
     */
    private $userSendContent;

    /**
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set email.
     *
     * @param string $email
     *
     * @return User
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get email.
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set active.
     *
     * @param bool $active
     *
     * @return User
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
     * Constructor.
     */
    public function __construct()
    {
        $this->user_groups = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Add user_groups.
     *
     * @param \Maith\NewsletterBundle\Entity\UserGroup $userGroups
     *
     * @return User
     */
    public function addUserGroup(\Maith\NewsletterBundle\Entity\UserGroup $userGroups)
    {
        $this->user_groups[] = $userGroups;

        return $this;
    }

    /**
     * Remove user_groups.
     *
     * @param \Maith\NewsletterBundle\Entity\UserGroup $userGroups
     */
    public function removeUserGroup(\Maith\NewsletterBundle\Entity\UserGroup $userGroups)
    {
        $this->user_groups->removeElement($userGroups);
    }

    /**
     * Get user_groups.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getUserGroups()
    {
        return $this->user_groups;
    }

    /**
     * Add contentUser.
     *
     * @param \Maith\NewsletterBundle\Entity\ContentUser $contentUser
     *
     * @return User
     */
    public function addContentUser(\Maith\NewsletterBundle\Entity\ContentUser $contentUser)
    {
        $this->contentUser[] = $contentUser;

        return $this;
    }

    /**
     * Remove contentUser.
     *
     * @param \Maith\NewsletterBundle\Entity\ContentUser $contentUser
     */
    public function removeContentUser(\Maith\NewsletterBundle\Entity\ContentUser $contentUser)
    {
        $this->contentUser->removeElement($contentUser);
    }

    /**
     * Get contentUser.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getContentUser()
    {
        return $this->contentUser;
    }

    /**
     * Add userSendContent.
     *
     * @param \Maith\NewsletterBundle\Entity\ContentSendUser $userSendContent
     *
     * @return User
     */
    public function addUserSendContent(\Maith\NewsletterBundle\Entity\ContentSendUser $userSendContent)
    {
        $this->userSendContent[] = $userSendContent;

        return $this;
    }

    /**
     * Remove userSendContent.
     *
     * @param \Maith\NewsletterBundle\Entity\ContentSendUser $userSendContent
     */
    public function removeUserSendContent(\Maith\NewsletterBundle\Entity\ContentSendUser $userSendContent)
    {
        $this->userSendContent->removeElement($userSendContent);
    }

    /**
     * Get userSendContent.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getUserSendContent()
    {
        return $this->userSendContent;
    }
}
