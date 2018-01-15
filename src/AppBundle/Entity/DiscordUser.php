<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * DiscordUser
 *
 * @ORM\Table(name="discord_user")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\DiscordUserRepository")
 */
class DiscordUser
{
    const AVATAR_PATTERN = 'https://cdn.discordapp.com/avatars/%u/%s.png';

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
     *
     * @ORM\Column(name="username", type="string", length=32)
     */
    private $username;

    /**
     * @var int
     *
     * @ORM\Column(name="discriminator", type="smallint")
     */
    private $discriminator;

    /**
     * @var string
     *
     * @ORM\Column(name="avatar", type="string", length=50)
     */
    private $avatar;

    /**
     * @var int
     *
     * @ORM\Column(name="userid", type="bigint", unique=true)
     */
    private $userid;

    /**
     * @ORM\OneToOne(targetEntity="AccessToken")
     */
    private $token;


    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set username
     *
     * @param string $username
     *
     * @return DiscordUser
     */
    public function setUsername($username)
    {
        $this->username = $username;

        return $this;
    }

    /**
     * Get username
     *
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * Set discriminator
     *
     * @param integer $discriminator
     *
     * @return DiscordUser
     */
    public function setDiscriminator($discriminator)
    {
        $this->discriminator = $discriminator;

        return $this;
    }

    /**
     * Get discriminator
     *
     * @return int
     */
    public function getDiscriminator()
    {
        return $this->discriminator;
    }

    /**
     * Set avatar
     *
     * @param string $avatar
     *
     * @return DiscordUser
     */
    public function setAvatar($avatar)
    {
        $this->avatar = $avatar;

        return $this;
    }

    /**
     * Get avatar
     *
     * @return string
     */
    public function getAvatar()
    {
        return $this->avatar;
    }

    /**
     * Set userid
     *
     * @param integer $userid
     *
     * @return DiscordUser
     */
    public function setUserid($userid)
    {
        $this->userid = $userid;

        return $this;
    }

    /**
     * Get userid
     *
     * @return int
     */
    public function getUserid()
    {
        return $this->userid;
    }

    /**
     * @return string
     */
    public function getAvatarAddress()
    {
        return sprintf(self::AVATAR_PATTERN, $this->userid, $this->avatar);
    }

    /**
     * @return AccessToken|null
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * @param AccessToken $token
     */
    public function setToken(AccessToken $token)
    {
        $this->token = $token;
    }
}

