<?php

namespace CanalTP\MttBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Template
 */
class Template extends AbstractEntity
{
    const STOP_TYPE = 'stop';
    const LINE_TYPE = 'line';

    // Available template types
    public static $templateTypes = array(
        self::STOP_TYPE,
        self::LINE_TYPE
    );

    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $type;

    /**
     * @var string
     */
    private $path;

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
     * Set type
     *
     * @param string $type
     * @throws \Exception if type is not known
     * @return Template
     */
    public function setType($type)
    {
        if (!in_array($type, self::$templateTypes))
            throw new \Exception($type);

        $this->type = $type;

        return $this;
    }

    /**
     * Get type
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set path
     *
     * @param string $path
     * @return Template
     */
    public function setPath($path)
    {
        $this->path = $path;

        return $this;
    }

    /**
     * Get path
     *
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }
}
