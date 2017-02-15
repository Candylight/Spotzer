<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Search
 *
 * @ORM\Table(name="search")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\SearchRepository")
 */
class Search
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
     * @ORM\Column(name="searchText", type="string", length=255, unique=true)
     */
    private $searchText;

    /**
     * @var string
     *
     * @ORM\Column(name="title", type="string", length=255, unique=false)
     */
    private $title;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="text", nullable=true)
     */
    private $description;

    /**
     * @var string
     *
     * @ORM\Column(name="urlImage", type="string", length=255, nullable=true)
     */
    private $urlImage;

    /**
     * @var string
     *
     * @ORM\Column(name="url", type="string", length=255, nullable=true)
     */
    private $url;

    /**
     * @var int
     *
     * @ORM\Column(name="count", type="integer")
     */
    private $count;

    /**
     * Search constructor.
     * @param string $searchText
     * @param string $title
     * @param string $description
     * @param string $urlImage
     * @param string $url
     * @param int $count
     */
    public function __construct($searchText, $title, $description, $urlImage, $url, $count)
    {
        $this->searchText = $searchText;
        $this->title = $title;
        $this->description = $description;
        $this->urlImage = $urlImage;
        $this->url = $url;
        $this->count = $count;
    }


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
     * Set searchText
     *
     * @param string $searchText
     *
     * @return Search
     */
    public function setSearchText($searchText)
    {
        $this->searchText = $searchText;

        return $this;
    }

    /**
     * Get searchText
     *
     * @return string
     */
    public function getSearchText()
    {
        return $this->searchText;
    }

    /**
     * Set description
     *
     * @param string $description
     *
     * @return Search
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set urlImage
     *
     * @param string $urlImage
     *
     * @return Search
     */
    public function setUrlImage($urlImage)
    {
        $this->urlImage = $urlImage;

        return $this;
    }

    /**
     * Get urlImage
     *
     * @return string
     */
    public function getUrlImage()
    {
        return $this->urlImage;
    }

    /**
     * Set url
     *
     * @param string $url
     *
     * @return Search
     */
    public function setUrl($url)
    {
        $this->url = $url;

        return $this;
    }

    /**
     * Get url
     *
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * Set count
     *
     * @param integer $count
     *
     * @return Search
     */
    public function setCount($count)
    {
        $this->count = $count;

        return $this;
    }

    /**
     * Get count
     *
     * @return int
     */
    public function getCount()
    {
        return $this->count;
    }

    /**
     * Set title
     *
     * @param string $title
     *
     * @return Search
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
}
