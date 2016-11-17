<?php

namespace CanalTP\MttBundle\Entity;

use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Layout
 */
class Layout extends AbstractEntity
{
    const ORIENTATION_LANDSCAPE = 0;
    const ORIENTATION_PORTRAIT = 1;
    const PAGE_SIZE_A3 = 'A3';
    const PAGE_SIZE_A4 = 'A4';
    const PAGE_SIZE_A5 = 'A5';

    /**
     * @var array
     */
    private $availablePageSizes = [
        self::PAGE_SIZE_A3,
        self::PAGE_SIZE_A4,
        self::PAGE_SIZE_A5,
    ];

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
    private $previewPath;

    /**
     * @var array
     */
    private $orientation = self::ORIENTATION_LANDSCAPE;

    /**
     * @var string
     */
    private $pageSize = self::PAGE_SIZE_A4;

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

    protected $file;

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
     * Set pageSize
     *
     * @throws \InvalidArgumentException
     * @param string $pageSize
     * @return Layout
     */
    public function setPageSize($pageSize)
    {
        $formattedPageSize = ucfirst(strtolower($pageSize));

        $this->checkPageSize($formattedPageSize);

        $this->pageSize = $formattedPageSize;

        return $this;
    }

    /**
     * Check if the pageSize property is valid
     *
     * @param $pageSize
     * @throws \InvalidArgumentException
     */
    private function checkPageSize($pageSize)
    {
        if (!in_array($pageSize, $this->availablePageSizes)) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Invalid pageSize, "%s" given. Available pageSize are %s.',
                    $pageSize,
                    implode(', ', $this->availablePageSizes)
                )
            );
        }
    }

    /**
     * Get pageSize
     *
     * @return string
     */
    public function getPageSize()
    {
        return $this->pageSize;
    }

    /**
     * Get orientation
     *
     * @return array
     */
    public function getOrientationAsString()
    {
        switch ($this->orientation) {
            case self::ORIENTATION_PORTRAIT:
                return 'portrait';
            case self::ORIENTATION_LANDSCAPE:
            default:
                return 'landscape';
        }
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
        return (string) $this->label;
    }

    public function getFile()
    {
        return $this->file;
    }

    public function setFile(UploadedFile $file = null)
    {
        $this->file = $file;

        return $this;
    }
}
