<?php
namespace CanalTP\MttBundle\Entity;

/**
 * Class Timecard
 * @package CanalTP\MttBundle\Entity
 */
class Timecard extends AbstractEntity
{

    /**
     * @var integer
     */
    private $id;

    /**
     * @var array
     */
    private $stopPoints;

    /**
     * @var \DateTime
     */
    protected $created;

    /**
     * @var \DateTime
     */
    protected $updated;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $timecardsPdf;

    /**
     * @var \CanalTP\NmmPortalBundle\Entity\Perimeter
     */
    private $perimeter;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->timecardsPdf = new \Doctrine\Common\Collections\ArrayCollection();
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
     * Set stopPoints
     *
     * @param array $stopPoints
     * @return Timecard
     */
    public function setStopPoints($stopPoints)
    {
        $this->stopPoints = $stopPoints;

        return $this;
    }

    /**
     * Get stopPoints
     *
     * @return array 
     */
    public function getStopPoints()
    {
        return $this->stopPoints;
    }

    /**
     * Set perimeter
     *
     * @param \CanalTP\NmmPortalBundle\Entity\Perimeter $perimeter
     * @return Timecard
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
     * Add timecardsPdf
     *
     * @param \CanalTP\MttBundle\Entity\TimecardPdf $timecardsPdf
     * @return Timecard
     */
    public function addTimecardsPdf(\CanalTP\MttBundle\Entity\TimecardPdf $timecardsPdf)
    {
        $this->timecardsPdf[] = $timecardsPdf;

        return $this;
    }

    /**
     * Remove timecardsPdf
     *
     * @param \CanalTP\MttBundle\Entity\TimecardPdf $timecardsPdf
     */
    public function removeTimecardsPdf(\CanalTP\MttBundle\Entity\TimecardPdf $timecardsPdf)
    {
        $this->timecardsPdf->removeElement($timecardsPdf);
    }

    /**
     * Get timecardsPdf
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getTimecardsPdf()
    {
        return $this->timecardsPdf;
    }
    /**
     * @var string
     */
    private $lineId;

    /**
     * @var string
     */
    private $routeId;

    /**
     * @var integer
     */
    private $seasonId;


    /**
     * Set lineId
     *
     * @param string $lineId
     * @return Timecard
     */
    public function setLineId($lineId)
    {
        $this->lineId = $lineId;

        return $this;
    }

    /**
     * Get lineId
     *
     * @return string 
     */
    public function getLineId()
    {
        return $this->lineId;
    }

    /**
     * Set routeId
     *
     * @param string $routeId
     * @return Timecard
     */
    public function setRouteId($routeId)
    {
        $this->routeId = $routeId;

        return $this;
    }

    /**
     * Get routeId
     *
     * @return string 
     */
    public function getRouteId()
    {
        return $this->routeId;
    }

    /**
     * Set seasonId
     *
     * @param integer $seasonId
     * @return Timecard
     */
    public function setSeasonId($seasonId)
    {
        $this->seasonId = $seasonId;

        return $this;
    }

    /**
     * Get seasonId
     *
     * @return integer 
     */
    public function getSeasonId()
    {
        return $this->seasonId;
    }
}
