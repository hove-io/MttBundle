<?php

namespace CanalTP\MttBundle\Entity;

use Symfony\Component\Validator\Mapping\ClassMetadata;
use CanalTP\MttBundle\Validator\Constraints\ConstainsNavitiaNetworkId;

/**
 * Network
 */
class Network extends AbstractEntity
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $externalId;

    /**
     * @var string
     */
    private $externalCoverageId;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $users;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $seasons;

    /**
     * @var Object
     */
    private $ditributionLists;

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
     * Set externalId
     *
     * @param  string  $externalId
     * @return Network
     */
    public function setExternalId($externalId)
    {
        $this->externalId = $externalId;

        return $this;
    }

    /**
     * Get externalId
     *
     * @return string
     */
    public function getExternalId()
    {
        return $this->externalId;
    }

    /**
     * Set externalCoverageId
     *
     * @param  string  $externalCoverageId
     * @return Network
     */
    public function setExternalCoverageId($externalCoverageId)
    {
        $this->externalCoverageId = $externalCoverageId;

        return $this;
    }

    /**
     * Get externalCoverageId
     *
     * @return string
     */
    public function getExternalCoverageId()
    {
        return $this->externalCoverageId;
    }

    /**
     * Get Object
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getSeasons()
    {
        return $this->seasons;
    }

    /**
     * Set Object
     *
     * @return Network
     */
    public function setSeasons($seasons)
    {
        $this->seasons = $seasons;

        return ($this);
    }

    /**
     * Set Object
     *
     * @return Network
     */
    public function addUser($user)
    {
        $this->users[] = $user;

        return ($this);
    }

    /**
     * Set distributionList
     *
     * @param integer $distributionList
     *
     * @return DistributionList
     */
    public function setDistributionList($distributionList)
    {
        $this->distributionList = $distributionList;

        return $this;
    }

    /**
     * Get distributionList
     *
     * @return Object
     */
    public function getDistributionList()
    {
        return $this->ditributionLists;
    }
}
