<?php

namespace CanalTP\MttBundle\Entity;

//use Doctrine\Common\Collections\ArrayCollection;

/**
 * Class LineTimecard
 * @package CanalTP\MttBundle\Entity
 */
class LineTimecard extends AbstractEntity
{
    const OBJECT_TYPE = 'lineTimecard';

    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $line_id;

    /**
     * @var \CanalTP\NmmPortalBundle\Entity\Perimeter
     */
    private $perimeter;

    /**
     * @var \CanalTP\MttBundle\Entity\LineConfig
     */
    private $line_config;

    /**
     * @var object $blocks
     */
    private $blocks;


    /*public function __construct()
    {
        $this->blocks = new ArrayCollection();
    }*/

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
     * Set line_id
     *
     * @param string $lineId
     * @return LineTimecard
     */
    public function setLineId($lineId)
    {
        $this->line_id = $lineId;

        return $this;
    }

    /**
     * Get line_id
     *
     * @return string 
     */
    public function getLineId()
    {
        return $this->line_id;
    }

    /**
     * Set perimeter
     *
     * @param \CanalTP\NmmPortalBundle\Entity\Perimeter $perimeter
     * @return LineTimecard
     */
    public function setPerimeter(\CanalTP\NmmPortalBundle\Entity\Perimeter $perimeter = null)
    {
        $this->perimeter = $perimeter;

        return $this;
    }

    /**
     * Get perimeter
     *
     * @return \CanalTP\NmmPortalBundle\Entity\Perimeter 
     */
    public function getPerimeter()
    {
        return $this->perimeter;
    }

    /**
     * Set line_config
     *
     * @param \CanalTP\MttBundle\Entity\LineConfig $lineConfig
     * @return LineTimecard
     */
    public function setLineConfig(\CanalTP\MttBundle\Entity\LineConfig $lineConfig = null)
    {
        $this->line_config = $lineConfig;

        return $this;
    }

    /**
     * Get line_config
     *
     * @return \CanalTP\MttBundle\Entity\LineConfig 
     */
    public function getLineConfig()
    {
        return $this->line_config;
    }


    /**
     * Set Blocks
     *
     * @param array $blocks
     *
     * @return LineTimecard
     */
    public function setBlocks($blocks)
    {
        $this->blocks = $blocks;
        /*foreach ($this->blocks as $block) {
            $block->setLineTimecard($this);
        }*/

        return $this;
    }

    /**
     * Get Blocks
     *
     * @return array of Block
     */
    public function getBlocks()
    {
        return $this->blocks;
    }

    public function isLocked()
    {
        return $this->getLineConfig()->isLocked();
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return self::OBJECT_TYPE;
    }
}
