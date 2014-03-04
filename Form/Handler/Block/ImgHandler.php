<?php

namespace CanalTP\MttBundle\Form\Handler\Block;

use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\Filesystem\Filesystem;
use Doctrine\Common\Persistence\ObjectManager;
use CanalTP\MediaManager\Category\CategoryType;
use CanalTP\MediaManagerBundle\DataCollector\MediaDataCollector as MediaManager;
use CanalTP\MediaManagerBundle\Entity\Category;
use CanalTP\MediaManagerBundle\Entity\Media;
use CanalTP\MttBundle\Entity\Block;

class ImgHandler extends AbstractHandler
{
    const ID_LINE_MAP = 'line_map';

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
        $timetableCategory = new Category($timetable->getId(), CategoryType::NETWORK);
        $routeCategory = new Category($timetable->getExternalRouteId(), CategoryType::LINE);
        $media = new Media();

        $routeCategory->setParent($timetableCategory);
        $media->setCategory($routeCategory);
        $media->setFile($formBlock->getContent());
        // TODO: Give img_label -> "ImgHandler::ID_LINE_MAP" as parameter
        $media->setFileName(ImgHandler::ID_LINE_MAP);

        $this->mediaManager->save($media);
        $formBlock->setContent($this->mediaManager->getUrlByMedia($media));
        $this->saveBlock($formBlock, $timetable);
    }
}
