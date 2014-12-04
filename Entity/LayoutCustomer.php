<?php

namespace CanalTP\MttBundle\Entity;

/**
 * LayoutCustomer
 */
class LayoutCustomer
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
    private $layout;

    /**
     * @var \stdClass
     */
    private $layoutsAssigned;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->layoutsAssigned = new \Doctrine\Common\Collections\ArrayCollection();
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
     * @param  \stdClass      $customer
     * @return LayoutCustomer
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
     * @param  \stdClass      $layout
     * @return LayoutCustomer
     */
    public function setLayout($layout)
    {
        $this->layout = $layout;

        return $this;
    }

    /**
     * Get layout
     *
     * @return \stdClass
     */
    public function getLayout()
    {
        return $this->layout;
    }

    /**
     * Set layoutsAssigned
     *
     * @param  \stdClass      $layoutsAssigned
     * @return LayoutCustomer
     */
    public function setLayoutsAssigned($layoutsAssigned)
    {
        $this->layoutsAssigned = $layoutsAssigned;

        return $this;
    }

    /**
     * Get layoutsAssigned
     *
     * @return \stdClass
     */
    public function getLayoutsAssigned()
    {
        return $this->layoutsAssigned;
    }

    /**
     * Set layoutsAssigned
     *
     * @param  \stdClass      $layoutsAssigned
     * @return LayoutCustomer
     */
    public function addLayoutAssigned($layoutsAssigned)
    {
        $this->layoutsAssigned->add($layoutsAssigned);

        return $this;
    }
}
