<?php

namespace CanalTP\MttBundle\Entity;

use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * LayoutConfig
 */
class LayoutConfig extends AbstractEntity
{
    const NOTES_MODE_AGGREGATED = 0;
    const NOTES_MODE_DISPATCHED = 1;

    const NOTES_TYPE_EXPONENT = 'exponent';
    const NOTES_TYPE_COLOR = 'color';

    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $label;

    /**
     * @var integer
     */
    private $calendarStart;

    /**
     * @var integer
     */
    private $calendarEnd;

    /**
     * @var integer
     */
    private $notesMode = self::NOTES_MODE_AGGREGATED;

    private $notesType;

    private $notesColors;

    /**
     * @var string
     */
    private $previewPath;

    /**
     * @var file
     */
    private $file;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $lineConfigs;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $perimeters;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $layout;

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
     * @param  string       $label
     * @return LayoutConfig
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
     * Set calendarStart
     *
     * @param  integer      $calendarStart
     * @return LayoutConfig
     */
    public function setCalendarStart($calendarStart)
    {
        $this->calendarStart = $calendarStart;

        return $this;
    }

    /**
     * Get calendarStart
     *
     * @return integer
     */
    public function getCalendarStart()
    {
        return $this->calendarStart;
    }

    /**
     * Set calendarEnd
     *
     * @param  integer      $calendarEnd
     * @return LayoutConfig
     */
    public function setCalendarEnd($calendarEnd)
    {
        $this->calendarEnd = $calendarEnd;

        return $this;
    }

    /**
     * Get calendarEnd
     *
     * @return integer
     */
    public function getCalendarEnd()
    {
        return $this->calendarEnd;
    }

    /**
     * Set notesMode
     *
     * @param  integer      $notesMode
     * @return LayoutConfig
     */
    public function setNotesMode($notesMode)
    {
        $this->notesMode = $notesMode;

        return $this;
    }

    /**
     * Get notesMode
     *
     * @return integer
     */
    public function getNotesMode()
    {
        return $this->notesMode;
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
        return ($this->previewPath ? $this->getWebPreviewPath() : $this->getLayout()->getPreviewPath());
    }

    public function aggregatesNotes()
    {
        return $this->getNotesMode() == self::NOTES_MODE_AGGREGATED;
    }

    public function dispatchesNotes()
    {
        return $this->getNotesMode() == self::NOTES_MODE_DISPATCHED;
    }

    public function getLineConfigs()
    {
        return ($this->lineConfigs);
    }

    public function setLineConfigs($lineConfigs)
    {
        $this->lineConfigs = $lineConfigs;

        return ($this);
    }

    /**
     * Set Networks
     *
     * @return Collections\Collection
     */
    public function getPerimeters()
    {
        return $this->perimeters;
    }

    /**
     * Set LayoutConfigs
     *
     * @return Network
     */
    public function addPerimeter($perimeters)
    {
        $this->perimeters[] = $perimeters;

        return $this;
    }

    /**
     * Set networks
     *
     * @return LayoutConfig
     */
    public function setPerimeters($perimeters)
    {
        $this->perimeters = $perimeters;

        return $this;
    }

    /**
     * Set Layout
     *
     * @return LayoutConfig
     */
    public function getLayout()
    {
        return ($this->layout);
    }

    /**
     * Set layout
     *
     * @return LayoutConfig
     */
    public function setLayout($layout)
    {
        $this->layout = $layout;

        return ($this);
    }

    /**
     * Set File
     *
     * @return LayoutConfig
     */
    public function getFile()
    {
        return ($this->file);
    }

    public function setNotesColors($notesColors)
    {
        $this->notesColors = $notesColors;

        return $this;
    }

    public function getNotesColors()
    {
        return ($this->notesColors);
    }

    public function setNotesType($notesType)
    {
        $this->notesType = $notesType;

        return $this;
    }

    public function getNotesType()
    {
        return $this->notesType;
    }

    /**
     * Set file
     *
     * @return LayoutConfig
     */
    public function setFile(UploadedFile $file = null)
    {
        $this->file = $file;

        return ($this);
    }

    public function upload()
    {
        if (null === $this->getFile()) {
            return;
        }
        $file = $this->getFile()->move(
            $this->getUploadRootDir(),
            $this->getFile()->getClientOriginalName()
        );
        $fileName = $this->getId() . '.' . $file->getExtension();
        $file->move(
            $this->getUploadRootDir(),
            $fileName
        );

        $this->previewPath = $fileName;
        $this->file = null;
    }

    public function getAbsolutePreviewPath()
    {
        return null === $this->previewPath
            ? null
            : $this->getUploadRootDir().'/'.$this->previewPath;
    }

    public function getWebPreviewPath()
    {
        return null === $this->previewPath
            ? null
            : $this->getUploadDir().'/'.$this->previewPath;
    }

    private function getUploadRootDir()
    {
        return __DIR__.'/../../../../../../web/'.$this->getUploadDir();
    }

    private function getUploadDir()
    {
        return '/uploads/layouts/previews/';
    }

    public function __toString()
    {
        return $this->label;
    }
}
