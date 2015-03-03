<?php

namespace CanalTP\MttBundle\Entity;

/**
 * Layout
 */
class Layout extends AbstractEntity
{
    const ORIENTATION_LANDSCAPE = 1;
    const ORIENTATION_PORTRAIT = 0;

    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $label;

    /**
     * @var string
     */
    private $path;

    /**
     * @var string
     */
    private $configuration;

    /**
     * @var string
     */
    private $previewPath;

    /**
     * @var array
     */
    private $orientation = self::ORIENTATION_LANDSCAPE;

    /**
     * @var array
     */
    private $notesModes;

    /**
     * @var integer
     */
    private $cssVersion;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $layoutConfigs;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $customers;

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
     * @param  string $label
     * @return Layout
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
     * Set customers
     *
     * @param  string       $customers
     * @return LayoutConfig
     */
    public function setCustomers($customers)
    {
        $this->customers = $customers;

        return $this;
    }

    /**
     * Get customers
     *
     * @return string
     */
    public function getCustomers()
    {
        return $this->customers;
    }

    /**
     * Set path
     *
     * @param  string $path
     * @return Layout
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

    /**
     * Set configuration
     *
     * @param $templateConf
     */
    public function setConfiguration($configuration)
    {
        $this->configuration = $configuration;
    }

    /**
     * Get configuration
     *
     * @return mixed
     */
    public function getConfiguration()
    {
        return $this->configuration;
    }

    /**
     * Set previewPath
     *
     * @param  string $previewPath
     * @return Layout
     */
    public function setPreviewPath($previewPath)
    {
        $this->previewPath = $previewPath;

        return $this;
    }

    /**
     * Get previewPath
     *
     * @return string
     */
    public function getPreviewPath()
    {
        return $this->previewPath;
    }

    /**
     * Set orientation
     *
     * @param  array  $orientation
     * @return Layout
     */
    public function setOrientation($orientation)
    {
        $this->orientation = $orientation;

        return $this;
    }

    /**
     * Get orientation
     *
     * @return array
     */
    public function getOrientation()
    {
        return $this->orientation;
    }

    /**
     * Get orientation
     *
     * @return array
     */
    public function getOrientationAsString($orientation = null)
    {

       $orientation = (is_null($orientation)) ? $this->orientation : $orientation;

        switch ($orientation) {
            case self::ORIENTATION_PORTRAIT:
                $orientationAsString = 'portrait';
                break;
            case self::ORIENTATION_LANDSCAPE:
            default:
                $orientationAsString = 'landscape';
                break;
        }

        return $orientationAsString;
    }

    /**
     * Set notesModes
     *
     * @param  array  $notesModes
     * @return Layout
     */
    public function setNotesModes($notesModes)
    {
        $this->notesModes = $notesModes;

        return $this;
    }

    /**
     * Get notesModes
     *
     * @return array
     */
    public function getNotesModes()
    {
        return $this->notesModes;
    }

    public function setCssVersion($cssVersion)
    {
        $this->cssVersion = $cssVersion;

        return $this;
    }

    /**
     * Get cssVersion
     *
     * @return integer
     */
    public function getCssVersion()
    {
        return $this->cssVersion;
    }

    /**
     * Set LayoutConfigs
     *
     * @return Collections\Collection
     */
    public function getLayoutConfigs()
    {
        return ($this->layoutConfigs);
    }

    /**
     * Set LayoutConfigs
     *
     * @return Layout
     */
    public function setLayoutConfigs($layoutConfigs)
    {
        $this->layoutConfigs = $layoutConfigs;

        return ($this);
    }

    public function __toString()
    {
        return $this->label;
    }
}
