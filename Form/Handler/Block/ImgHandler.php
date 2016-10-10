<?php

namespace CanalTP\MttBundle\Form\Handler\Block;

use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Filesystem\Filesystem;
use Doctrine\Common\Persistence\ObjectManager;

use CanalTP\MttBundle\Services\MediaManager;
use CanalTP\MediaManagerBundle\Entity\Media;
use CanalTP\MttBundle\Entity\Block;
use CanalTP\MttBundle\Form\TYPE\Block\ImgType;

class ImgHandler extends AbstractHandler
{
    private $mediaManager = null;

    public function __construct(
        ObjectManager $om,
        MediaManager $mediaManager,
        Block $block
    ) {
        $this->om = $om;
        $this->mediaManager = $mediaManager;
        $this->block = $block;
    }

    public function process(Block $formBlock, $timetable)
    {
        $file = $formBlock->getContent();
        // convert into png if a jpeg was given
        if ($file->getMimeType() == ImgType::MIME_IMAGETYPE_JPEG) {
            $input = imagecreatefromjpeg($file->getRealPath());
            list($width, $height) = getimagesize($file->getRealPath());
            $output = imagecreatetruecolor($width, $height);
            imagecopy($output, $input, 0, 0, 0, 0, $width, $height);
            imagepng($output, $file->getRealPath() . '.png');
            imagedestroy($output);
            imagedestroy($input);
            $pngFile = new File($file->getRealPath() . '.png');
        } else {
            // force extension png in lowercase
            $pngFile = $file->move($file->getPath(), $file->getRealPath() . '.png');
        }
        $media = $this->mediaManager->saveByTimetable($timetable, $pngFile, $this->block->getDomId());
        // TODO: saved with domain, we should store without it. Waiting for mediaDataCollector to be updated
        $formBlock->setContent($this->mediaManager->getUrlByMedia($media) . '?' . time());
        $this->saveBlock($formBlock, $timetable);
    }
}
