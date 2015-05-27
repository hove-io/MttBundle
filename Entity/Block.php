<?php

namespace CanalTP\MttBundle\Entity;
use CanalTP\MttBundle\Entity\Timetable;
use CanalTP\MttBundle\Entity\LineTimecard;

/**
 * Block
 */
class Block extends AbstractEntity
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $typeId;

    /**
     * @var string
     */
    private $domId;

    /**
     * @var string
     */
    private $content;

    /**
     * @var string
     */
    private $title;

    /**
     * @var Timetable Object
     */
    private $timetable;

    /**
     * @var LineTimecard Object
     */
    private $lineTimecard;

    /**
     * @var Object
     */
    private $stopPoint;

    /**
     * @var Object
     */
    private $frequencies;

    /**
     * @var string $color
     */
    private $color;

    /**
     * @var string $route
     */
    private $route;

    public function __construct()
    {
        $this->frequencies = new \Doctrine\Common\Collections\ArrayCollection();
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
     * Get typeId
     *
     * @return string
     */
    public function getTypeId()
    {
        return $this->typeId;
    }

    /**
     * Set typeId
     *
     * @param  string $typeId
     * @return Block
     */
    public function setTypeId($typeId)
    {
        $this->typeId = $typeId;

        return $this;
    }

    /**
     * Set domId
     *
     * @param  string $domId
     * @return Block
     */
    public function setDomId($domId)
    {
        $this->domId = $domId;

        return $this;
    }

    /**
     * Get domId
     *
     * @return string
     */
    public function getDomId()
    {
        return $this->domId;
    }

    /**
     * Set content
     *
     * @param  string $content
     * @return Block
     */
    public function setContent($content)
    {
        $this->content = $content;

        return $this;
    }

    /**
     * Get content
     *
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * Set title
     *
     * @param  string $title
     * @return Block
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

    /**
     * Set timetable
     *
     * @param integer $timetable
     *
     * @return Block
     */
    public function setTimetable($timetable)
    {
        $this->timetable = $timetable;

        return $this;
    }

    /**
     * Get timetable
     *
     * @return string
     */
    public function getTimetable()
    {
        return $this->timetable;
    }

    /**
     * Set lineTimecard
     *
     * @param integer $lineTimecard
     *
     * @return Block
     */
    public function setLineTimecard($lineTimecard)
    {
        $this->lineTimecard = $lineTimecard;

        return $this;
    }

    /**
     * Get lineTimecard
     *
     * @return string
     */
    public function getLineTimecard()
    {
        return $this->lineTimecard;
    }

    /**
     * Set stopPoint
     *
     * @param integer $stopPoint
     *
     * @return Block
     */
    public function setStopPoint($stopPoint)
    {
        $this->stopPoint = $stopPoint;

        return $this;
    }

    /**
     * Get stopPoint
     *
     * @return string
     */
    public function getStopPoint()
    {
        return $this->stopPoint;
    }

    /**
     * Set frequencies
     *
     * @param array $frequencies
     *
     * @return Block
     */
    public function setFrequencies($frequencies)
    {
        $this->frequencies = $frequencies;
        foreach ($this->frequencies as $frequency) {
            $frequency->setBlock($this);
        }

        return $this;
    }

    /**
     * Get frequencies
     *
     * @return array
     */
    public function getFrequencies()
    {
        return $this->frequencies;
    }

    /**
     * Set color
     *
     * @param integer $color
     *
     * @return Block
     */
    public function setColor($color)
    {
        $this->color = $color;

        return $this;
    }

    /**
     * Get color
     *
     * @return string
     */
    public function getColor()
    {
        return $this->color;
    }


    /**
     * Set route
     *
     * @param integer $route
     *
     * @return Block
     */
    public function setRoute($route)
    {
        $this->route = $route;

        return $this;
    }

    /**
     * Get route
     *
     * @return string
     */
    public function getRoute()
    {
        return $this->route;
    }

    /**
     * Check if it is Img block
     *
     * @return boolean
     */
    public function isImg()
    {
        return ($this->getTypeId() == BlockRepository::IMG_TYPE);
    }

    /**
     * Check if it is svg image
     * @return bool
     */
    public function isImgSvg() {
        if (preg_match("/\.svg/", $this->content)) {
            return true;
        }
        return false;
    }

    /**
     * Get svg content file
     * @return string
     */
    public function getSvgContent() {
        $svg = file_get_contents($this->content);
        return $svg;
    }

    /**
     * Get base64 svg content file
     * @return string
     */
    public function getBase64SvgContent() {
        $svg = base64_encode(file_get_contents($this->content));
        return $svg;
    }

    /**
     * Check if it is Text block
     *
     * @return boolean
     */
    public function isText()
    {
        return ($this->getTypeId() == BlockRepository::TEXT_TYPE);
    }

    /**
     * Check if it is Calendar block
     *
     * @return boolean
     */
    public function isCalendar()
    {
        return ($this->getTypeId() == BlockRepository::CALENDAR_TYPE);
    }

    public function isLocked()
    {
        if ($this->getTimetable() instanceof Timetable) {
            return $this->getTimetable()->isLocked();
        } else if ($this->getLineTimecard() instanceof LineTimecard) {
            return $this->getLineTimecard()->isLocked();
        }
    }


}
