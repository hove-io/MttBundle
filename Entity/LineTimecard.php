<?php

namespace CanalTP\MttBundle\Entity;


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

    /**
     * @var object $timecards
     */
    private $timecards;

    /**
     * @var string $hashPdf
     */
    private $hash_pdf;

    /**
     * @var datetime
     */
    protected $pdfGenerationDate;

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

    /**
     * Set Timecards
     *
     * @param array $timecards
     *
     * @return LineTimecard
     */
    public function setTimecards($timecards)
    {
        $this->timecards = $timecards;

        return $this;
    }

    /**
     * Get Timecards
     *
     * @return array of Timecards
     */
    public function getTimecards()
    {
        return $this->timecards;
    }

    public function isLocked()
    {
        return $this->getLineConfig()->isLocked();
    }

    /**
     * Set hash Pdf
     * @param string $hashPdf
     */
    public function setPdfHash($hashPdf)
    {
       $this->hash_pdf = $hashPdf;
    }

    /**
     * Get hash pdf
     * @return string
     */
    public function getPdfHash()
    {
        return $this->hash_pdf;
    }

    /**
     * @param $datetime
     */
    public function setPdfGenerationDate($datetime)
    {
        $this->pdfGenerationDate = $datetime;
    }

    /**
     * Get pdfGenerationDate
     *
     * @return string
     */
    public function getPdfGenerationDate()
    {
        return ($this->pdfGenerationDate);
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return self::OBJECT_TYPE;
    }
}
