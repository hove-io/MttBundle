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
    private $coverageId;
    
    /**
     * @var string
     */
    private $networkId;
    
    /**
     * @var string
     */
    private $navitiaLineId;
    
    /**
     * @var string
     */
    private $layout;
    
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
     * Set navitiaLineId
     *
     * @param string $navitiaLineId
     * @return Line
     */
    public function setNavitiaLineId($navitiaLineId)
    {
        $this->navitiaLineId = $navitiaLineId;
    
        return $this;
    }

    /**
     * Get getNavitiaLineId
     *
     * @return string 
     */
    public function getNavitiaLineId()
    {
        return $this->navitiaLineId;
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
}