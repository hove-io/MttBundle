<?php

namespace CanalTP\MttBundle\Entity;

/**
 * Frequency
 */
class Frequency extends AbstractEntity
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var \DateTime
     */
    private $startTime;

    /**
     * @var \DateTime
     */
    private $endTime;

    /**
     * @var string
     */
    private $content;

    /**
     * @var integer
     */
    private $columns;

    /**
     * @var integer
     */
    private $time;

    /**
     * @var Block
     */
    private $block;

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
     * Set startTime
     *
     * @param  \DateTime $startTime
     * @return Frequency
     */
    public function setStartTime($startTime)
    {
        $this->startTime = $startTime;

        return $this;
    }

    /**
     * Get startTime
     *
     * @return \DateTime
     */
    public function getStartTime()
    {
        return $this->startTime;
    }

    /**
     * Set endTime
     *
     * @param  \DateTime $endTime
     * @return Frequency
     */
    public function setEndTime($endTime)
    {
        $this->endTime = $endTime;

        return $this;
    }

    /**
     * Get endTime
     *
     * @return \DateTime
     */
    public function getEndTime()
    {
        return $this->endTime;
    }

    /**
     * Set content
     *
     * @param  string    $content
     * @return Frequency
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
     * Set columns
     *
     * @param integer $columns
     * @return Frequency
     */
    public function setColumns($columns)
    {
        $this->columns = $columns;

        return $this;
    }

    /**
     * Get columns
     *
     * @return integer
     */
    public function getColumns()
    {
        return $this->columns;
    }

    /**
     * Set time
     *
     * @param integer $time
     * @return Frequency
     */
    public function setTime($time)
    {
        $this->time = $time;

        return $this;
    }

    /**
     * Get time
     *
     * @return integer
     */
    public function getTime()
    {
        return $this->time;
    }

    /**
     * Set block
     *
     * @param Block $block
     * @return Frequency
     */
    public function setBlock($block)
    {
        $this->block = $block;

        return $this;
    }

    /**
     * Get block
     *
     * @return Block
     */
    public function getBlock()
    {
        return $this->block;
    }
}
