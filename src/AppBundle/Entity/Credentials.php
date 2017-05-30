<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\OneToOne;

/**
 * Credentials
 *
 * @ORM\Table(name="credentials")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\CredentialsRepository")
 */
class Credentials
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
     *
     * @ORM\Column(name="youtubeToken", type="string", length=255, nullable=true)
     */
    private $youtubeToken;

    /**
     * @var string
     *
     * @ORM\Column(name="youtubeRefreshToken", type="string", length=255, nullable=true)
     */
    private $youtubeRefreshToken;

    /**
     * @var string
     *
     * @ORM\Column(name="spotifyToken", type="string", length=255, nullable=true)
     */
    private $spotifyToken;

    /**
     * @var string
     *
     * @ORM\Column(name="deezerToken", type="string", length=255, nullable=true)
     */
    private $deezerToken;

    /**
     * @var string
     *
     * @ORM\Column(name="soundcloudToken", type="string", length=255, nullable=true)
     */
    private $soundcloudToken;

    /**
     * @OneToOne(targetEntity="AppBundle\Entity\User", inversedBy="credentials")
     * @JoinColumn(name="user_id", referencedColumnName="id")
     */
    private $user;

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
     * Set youtubeToken
     *
     * @param string $youtubeToken
     *
     * @return Credentials
     */
    public function setYoutubeToken($youtubeToken)
    {
        $this->youtubeToken = $youtubeToken;

        return $this;
    }

    /**
     * Get youtubeToken
     *
     * @return string
     */
    public function getYoutubeToken()
    {
        return $this->youtubeToken;
    }

    /**
     * Set spotifyToken
     *
     * @param string $spotifyToken
     *
     * @return Credentials
     */
    public function setSpotifyToken($spotifyToken)
    {
        $this->spotifyToken = $spotifyToken;

        return $this;
    }

    /**
     * Get spotifyToken
     *
     * @return string
     */
    public function getSpotifyToken()
    {
        return $this->spotifyToken;
    }

    /**
     * Set deezerToken
     *
     * @param string $deezerToken
     *
     * @return Credentials
     */
    public function setDeezerToken($deezerToken)
    {
        $this->deezerToken = $deezerToken;

        return $this;
    }

    /**
     * Get deezerToken
     *
     * @return string
     */
    public function getDeezerToken()
    {
        return $this->deezerToken;
    }

    /**
     * Set soundcloudToken
     *
     * @param string $soundcloudToken
     *
     * @return Credentials
     */
    public function setSoundcloudToken($soundcloudToken)
    {
        $this->soundcloudToken = $soundcloudToken;

        return $this;
    }

    /**
     * Get soundcloudToken
     *
     * @return string
     */
    public function getSoundcloudToken()
    {
        return $this->soundcloudToken;
    }

    /**
     * Set user
     *
     * @param \AppBundle\Entity\User $user
     *
     * @return Credentials
     */
    public function setUser(\AppBundle\Entity\User $user = null)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return \AppBundle\Entity\User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set youtubeRefreshToken
     *
     * @param string $youtubeRefreshToken
     *
     * @return Credentials
     */
    public function setYoutubeRefreshToken($youtubeRefreshToken)
    {
        $this->youtubeRefreshToken = $youtubeRefreshToken;

        return $this;
    }

    /**
     * Get youtubeRefreshToken
     *
     * @return string
     */
    public function getYoutubeRefreshToken()
    {
        return $this->youtubeRefreshToken;
    }
}
