<?php

namespace CanalTP\MttBundle\Entity;

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
     * @var string
     */
    private $token;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $users;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $layoutConfigs;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $seasons;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $amqpTasks;

    /**
     * @var Object
     */
    private $ditributionLists;

    /**
     * @var Object
     */
    private $areas;

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
     * Set token
     *
     * @param  string  $token
     * @return Network
     */
    public function setToken($token)
    {
        $this->token = $token;

        return $this;
    }

    /**
     * Get token
     *
     * @return string
     */
    public function getToken()
    {
        return $this->token;
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
     * Get Object
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getAmqpTasks()
    {
        return $this->amqpTasks;
    }

    /**
     * Set Object
     *
     * @return Network
     */
    public function setAmqpTasks($amqpTasks)
    {
        $this->amqpTasks = $amqpTasks;

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
     * Set Object
     *
     * @return Network
     */
    public function getLayoutConfigs()
    {
        return ($this->layouts);
    }

    /**
     * Set LayoutConfigs
     *
     * @return Network
     */
    public function addLayoutConfig($layout)
    {
        $this->layouts[] = $layout;

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

    //custom title
    public function getName()
    {
        $explodedId = explode(':', $this->getExternalId());

        return count($explodedId) > 0 ? $explodedId[1] : $this->getExternalId();
    }

    /**
     * Set areas
     *
     * @param  array  $areas
     * @return Network
     */
    public function setAreas($areas)
    {
        $this->areas = $areas;

        return $this;
    }

    /**
     * Get areas
     *
     * @return array
     */
    public function getAreas()
    {
        return $this->areas;
    }
}
