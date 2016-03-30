<?php

namespace CanalTP\MttBundle\Entity;

/**
 * AmqpAck
 */
class AmqpAck extends AbstractEntity
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var \stdClass
     */
    private $payload;

    /**
     * @var array
     */
    private $deliveryInfo;

    /**
     * @var array
     */
    private $amqpTask;

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
     * Set payload
     *
     * @param  \stdClass $payload
     * @return AmqpAck
     */
    public function setPayload($payload)
    {
        $this->payload = $payload;

        return $this;
    }

    /**
     * Get payload
     *
     * @return \stdClass
     */
    public function getPayload()
    {
        return $this->payload;
    }

    /**
     * Set deliveryInfo
     *
     * @param  array   $deliveryInfo
     * @return AmqpAck
     */
    public function setDeliveryInfo($deliveryInfo)
    {
        $this->deliveryInfo = $deliveryInfo;

        return $this;
    }

    /**
     * Get deliveryInfo
     *
     * @return array
     */
    public function getDeliveryInfo()
    {
        return $this->deliveryInfo;
    }

    /**
     * Set amqpTask
     *
     * @param  \stdClass $amqpTask
     * @return AmqpAck
     */
    public function setAmqpTask($amqpTask)
    {
        $this->amqpTask = $amqpTask;

        return $this;
    }

    /**
     * Get amqpTask
     *
     * @return \stdClass
     */
    public function getAmqpTask()
    {
        return $this->amqpTask;
    }
}
