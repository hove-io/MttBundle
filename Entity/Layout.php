<?php

namespace CanalTP\MttBundle\Entity;

use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Layout
 */
class Layout extends AbstractEntity
{
    const ORIENTATION_LANDSCAPE = 0;
    const ORIENTATION_PORTRAIT = 1;

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
    private $orientation = self::ORIENTATION_LANDSCAPE;

    /**
     * @var string
     */
    private $previewPath;

    /**
     * @var array
     */
    private $notesModes;

    /**
     * @var integer
     */
    private $cssVersion;

    /**
     * @var Collection
     */
    private $layoutConfigs;

    /**
     * @var Collection
     */
    private $customers;

    /**
     * @var Collection
     */
    private $templates;

    protected $file;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->layoutConfigs = new ArrayCollection();
        $this->customers = new ArrayCollection();
        $this->templates = new ArrayCollection();
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
     * Set previewPath
     *
     * @param string $previewPath
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
     * Get orientation as string
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
     * Set customers
     *
     * @param Collection $customers
     * @return LayoutConfig
     */
    public function setCustomers(Collection $customers)
    {
        $this->customers = $customers;

        return $this;
    }

    /**
     * Get customers
     *
     * @return Collection
     */
    public function getCustomers()
    {
        return $this->customers;
    }


    /**
     * Set LayoutConfigs
     *
     * @return Collection
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

    /**
     * Get file
     *
     * @return Layout
     */
    public function getFile()
    {
        return $this->file;

        return $this;
    }

    /**
     * Set file
     *
     * @param string $file
     */
    public function setFile(UploadedFile $file = null)
    {
        $this->file = $file;

        return $this;
    }

    /**
     * Get templates
     *
     * @return Collection
     */
    public function getTemplates()
    {
        return $this->templates;
    }

    /**
     * Get template
     *
     * @param string $type
     * @return Template or null
     */
    public function getTemplate($type)
    {
        foreach ($this->templates as $template)
        {
            if ($template->getType() == $type)
                return $template;
        }

        return null;
    }

    /**
     * Get templatesTypes
     *
     * @return array
     */
    public function getTemplatesTypes()
    {
        $templateTypes = array();

        foreach ($this->templates as $template)
            $templateTypes[] = $template->getType();

        return $templateTypes;
    }

    /**
     * Set templates
     *
     * @param Collection $templates
     * @return Layout
     */
    public function setTemplates(Collection $templates)
    {
        $this->templates = $templates;

        return $this;
    }

    /**
     * Add template
     *
     * @param Template $template
     * @return Layout
     */
    public function addTemplate(Template $template)
    {
        $this->templates->add($template);

        return $this;
    }

    /**
     * Remove template
     *
     * @param Template $template
     * @return Layout
     */
    public function removeTemplate(Template $template)
    {
        $this->templates->removeElement($template);

        return $this;
    }

    public function __toString()
    {
        return $this->label;
    }
}
