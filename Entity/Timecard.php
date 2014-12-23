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
     * @var string
     */
    private $label;

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
    private $areasPdf;

    /**
     * @var \CanalTP\NmmPortalBundle\Entity\Perimeter
     */
    private $perimeter;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->areasPdf = new \Doctrine\Common\Collections\ArrayCollection();
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
     * Set label
     *
     * @param string $label
     * @return Timecard
     */
    public function setLabel($label)
    {
        $this->label = $label;

        return $this;
    }

    /**
     * Get label
     *
     * @return string 
     */
    public function getLabel()
    {
        return $this->label;
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
     * Set created
     *
     * @param \DateTime $created
     * @return Timecard
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
     * @return Timecard
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
     * Add areasPdf
     *
     * @param \CanalTP\MttBundle\Entity\TimecardPdf $areasPdf
     * @return Timecard
     */
    public function addAreasPdf(\CanalTP\MttBundle\Entity\TimecardPdf $areasPdf)
    {
        $this->areasPdf[] = $areasPdf;

        return $this;
    }

    /**
     * Remove areasPdf
     *
     * @param \CanalTP\MttBundle\Entity\TimecardPdf $areasPdf
     */
    public function removeAreasPdf(\CanalTP\MttBundle\Entity\TimecardPdf $areasPdf)
    {
        $this->areasPdf->removeElement($areasPdf);
    }

    /**
     * Get areasPdf
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getAreasPdf()
    {
        return $this->areasPdf;
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
}
