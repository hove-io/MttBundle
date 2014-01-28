<?php

namespace CanalTP\MethBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Line
 */
class Line
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $title;

    /**
     * @var string
     */
    private $coverageId;
    
    /**
     * @var string
     */
    private $networkId;
    
    /**
     * @var string
     */
    private $navitiaId;
    
    /**
     * @var string
     */
    private $layout;

    /**
     * @var string
     */
    private $twigPath;

    /**
     * @var Array
     */
    private $blocks;
    
    /**
     * Set id
     *
     * @return Object 
     */
    public function setId($id)
    {
        $this->id = $id;
        
        return $this;
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
     * Get title
     *
     * @return string 
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set title
     *
     * @param string $networkId
     * @return Line
     */
    public function setTitle($title)
    {
        $this->title = $title;
    
        return $this;
    }

    /**
     * Set coverageId
     *
     * @param string $coverageId
     * @return Line
     */
    public function setCoverageId($coverageId)
    {
        $this->coverageId = $coverageId;
    
        return $this;
    }

    /**
     * Get nameId
     *
     * @return string 
     */
    public function getCoverageId()
    {
        return $this->coverageId;
    }

    /**
     * Set networkId
     *
     * @param string $networkId
     * @return Line
     */
    public function setNetworkId($networkId)
    {
        $this->networkId = $networkId;
    
        return $this;
    }

    /**
     * Get networkId
     *
     * @return string 
     */
    public function getNetworkId()
    {
        return $this->networkId;
    }

    /**
     * Set navitiaId
     *
     * @param string $navitiaId
     * @return Line
     */
    public function setNavitiaId($navitiaId)
    {
        $this->navitiaId = $navitiaId;
    
        return $this;
    }

    /**
     * Get getNavitiaId
     *
     * @return string 
     */
    public function getNavitiaId()
    {
        return $this->navitiaId;
    }
    
    /**
     * Set layout
     *
     * @param string $layout
     * @return Line
     */
    public function setLayout($layout)
    {
        $this->layout = $layout;
    
        return $this;
    }

    /**
     * Get getLayout
     *
     * @return string 
     */
    public function getLayout()
    {
        return $this->layout;
    }
    
    /**
     * Set blocks
     *
     * @param array $blocks
     * @return Line
     */
    public function setBlocks($blocks)
    {
        $this->blocks = $blocks;
    
        return $this;
    }

    /**
     * Get blocks
     *
     * @return array 
     */
    public function getBlocks()
    {
        return $this->blocks;
    }
        
    /**
     * Get twigPath
     *
     * @return string
     */
    public function getTwigPath()
    {
        return ($this->twigPath);
    }

    /**
     * Set twigPath
     *
     * @param string $twigPath
     * @return Line
     */
    public function setTwigPath($twigPath)
    {
        $this->twigPath = $twigPath;
    
        return $this;
    }
}