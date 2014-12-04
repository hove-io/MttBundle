<?php

namespace CanalTP\MttBundle\Model;

use Symfony\Component\HttpFoundation\File\UploadedFile;

class LayoutModel
{
    protected $file;

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
