<?php

namespace CanalTP\MethBundle\Form\Handler\Block;

use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\Filesystem\Filesystem;
use Doctrine\Common\Persistence\ObjectManager;
use CanalTP\MediaManager\Category\CategoryType;
use CanalTP\MediaManagerBundle\DataCollector\MediaDataCollector as MediaManager;
use CanalTP\MediaManagerBundle\Entity\Media;
use CanalTP\MethBundle\Entity\Block;

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
    private function removeOldImg(Filesystem $fs, $destDir)
    {
        $oldPath = $destDir . $this->lastImgPath;

        if ($fs->exists($oldPath)) {
            $fs->remove($oldPath);
        }
    }

    public function process(Block $formBlock, $timetable)
    {
        $media = new Media(
            // TODO: get network id
            CategoryType::NETWORK,
            $timetable->getId(),
            // TODO: change Category Type to Route
            CategoryType::LINE,
            $timetable->getExternalRouteId()
        );

        $media->setFile($formBlock->getContent());
        $this->mediaManager->save($media);
        $formBlock->setContent($this->mediaManager->getUrlByMedia($media));
        $this->saveBlock($formBlock, $timetable);
    }
}
