<?php
namespace CanalTP\MttBundle\Entity;

/**
 * Class TimecardPdf
 * @package CanalTP\MttBundle\Entity
 */
class TimecardPdf extends AbstractEntity
{

    /**
     * @var integer
     */
    private $id;

    /**
     * @var \DateTime
     */
    private $generatedAt;

    /**
     * @var \DateTime
     */
    protected $created;

    /**
     * @var \DateTime
     */
    protected $updated;

    /**
     * @var \CanalTP\MttBundle\Entity\Timecard
     */
    private $timecard;

    /**
     * @var \CanalTP\MttBundle\Entity\Season
     */
    private $season;


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
     * Set generatedAt
     *
     * @param \DateTime $generatedAt
     * @return TimecardPdf
     */
    public function setGeneratedAt($generatedAt)
    {
        $this->generatedAt = $generatedAt;

        return $this;
    }

    /**
     * Get generatedAt
     *
     * @return \DateTime 
     */
    public function getGeneratedAt()
    {
        return $this->generatedAt;
    }

    /**
     * Set created
     *
     * @param \DateTime $created
     * @return TimecardPdf
     */
    public function setCreated($created)
    {
        $this->created = $created;

        return $this;
    }

    /**
     * Get created
     *
     * @return \DateTime 
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * Set updated
     *
     * @param \DateTime $updated
     * @return TimecardPdf
     */
    public function setUpdated($updated)
    {
        $this->updated = $updated;

        return $this;
    }

    /**
     * Get updated
     *
     * @return \DateTime 
     */
    public function getUpdated()
    {
        return $this->updated;
    }

    /**
     * Set timecard
     *
     * @param \CanalTP\MttBundle\Entity\Timecard $timecard
     * @return TimecardPdf
     */
    public function setTimecard(\CanalTP\MttBundle\Entity\Timecard $timecard = null)
    {
        $this->timecard = $timecard;

        return $this;
    }

    /**
     * Get timecard
     *
     * @return \CanalTP\MttBundle\Entity\Timecard 
     */
    public function getTimecard()
    {
        return $this->timecard;
    }

    /**
     * Set season
     *
     * @param \CanalTP\MttBundle\Entity\Season $season
     * @return TimecardPdf
     */
    public function setSeason(\CanalTP\MttBundle\Entity\Season $season = null)
    {
        $this->season = $season;

        return $this;
    }

    /**
     * Get season
     *
     * @return \CanalTP\MttBundle\Entity\Season 
     */
    public function getSeason()
    {
        return $this->season;
    }
}
