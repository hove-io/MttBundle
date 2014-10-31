<?php

namespace CanalTP\MttBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * LayoutConfigCustomer
 */
class LayoutConfigCustomer
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var \stdClass
     */
    private $customer;

    /**
     * @var \stdClass
     */
    private $layoutConfig;

    /**
     * @var \stdClass
     */
    private $layoutConfigsAssigned;


    /**
     * Constructor
     */
    public function __construct()
    {
        $this->layoutConfigsAssigned = new \Doctrine\Common\Collections\ArrayCollection();
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
     * Set customer
     *
     * @param \stdClass $customer
     * @return LayoutConfigCustomer
     */
    public function setCustomer($customer)
    {
        $this->customer = $customer;

        return $this;
    }

    /**
     * Get customer
     *
     * @return \stdClass
     */
    public function getCustomer()
    {
        return $this->customer;
    }

    /**
     * Set customer
     *
     * @param \stdClass $layoutConfig
     * @return LayoutConfigCustomer
     */
    public function setLayoutConfig($layoutConfig)
    {
        $this->layoutConfig = $layoutConfig;

        return $this;
    }

    /**
     * Get layoutConfig
     *
     * @return \stdClass
     */
    public function getLayoutConfig()
    {
        return $this->layoutConfig;
    }

    /**
     * Set layoutConfigsAssigned
     *
     * @param \stdClass $layoutConfigsAssigned
     * @return LayoutConfigCustomer
     */
    public function setLayoutConfigsAssigned($layoutConfigsAssigned)
    {
        $this->layoutConfigsAssigned = $layoutConfigsAssigned;

        return $this;
    }

    /**
     * Get layoutConfigsAssigned
     *
     * @return \stdClass
     */
    public function getLayoutConfigsAssigned()
    {
        return $this->layoutConfigsAssigned;
    }

    /**
     * Set layoutConfigsAssigned
     *
     * @param \stdClass $layoutConfigsAssigned
     * @return LayoutConfigCustomer
     */
    public function addLayoutConfigAssigned($layoutConfigsAssigned)
    {
        $this->layoutConfigsAssigned->add($layoutConfigsAssigned);

        return $this;
    }
}
