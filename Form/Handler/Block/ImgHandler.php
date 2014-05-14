<?php

namespace CanalTP\MttBundle\Form\Handler\Block;

use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\Filesystem\Filesystem;
use Doctrine\Common\Persistence\ObjectManager;

use CanalTP\MediaManager\Category\CategoryType;
use CanalTP\MttBundle\Services\MediaManager;
use CanalTP\MediaManagerBundle\Entity\Category;
use CanalTP\MediaManagerBundle\Entity\Media;
use CanalTP\MttBundle\Entity\Block;

class ImgHandler extends AbstractHandler
{
    private $co = null;
    private $lastImgPath = null;
    private $mediaManager = null;

    public function __construct(
        Container $co,
        ObjectManager $om,
        MediaManager $mediaManager,
        $block,
        $lastImgPath
    )
    {
        $this->co = $co;
        $this->om = $om;
        $this->mediaManager = $mediaManager;
        $this->block = $block;
        $this->lastImgPath = $lastImgPath;
    }

    // Remove previous file. Pb was: block->content already has new value
    // private function removeOldImg(Filesystem $fs, $destDir)
    // {
        // $oldPath = $destDir . $this->lastImgPath;

        // if ($fs->exists($oldPath)) {
            // $fs->remove($oldPath);
        // }
    // }

    public function process(Block $formBlock, $timetable)
    {
        $media = $this->mediaManager->saveByTimetable($timetable, $formBlock->getContent(), $this->block->getDomId());
        // TODO: save with domain, we should store without it. Waiting for mediaDataCollector to be updated
        $formBlock->setContent($this->mediaManager->getUrlByMedia($media));
        $this->saveBlock($formBlock, $timetable);
    }
}
