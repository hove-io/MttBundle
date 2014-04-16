<?php

namespace CanalTP\MttBundle\Services;

use Symfony\Component\HttpFoundation\File\File;
use CanalTP\MttBundle\Form\Handler\Block\ImgHandler;
use CanalTP\MediaManager\Category\CategoryType;
use CanalTP\MediaManagerBundle\DataCollector\MediaDataCollector;
use CanalTP\MediaManagerBundle\Entity\Category;
use CanalTP\MediaManagerBundle\Entity\Media;
use CanalTP\MttBundle\Entity\Block;

class MediaManager
{
    private $mediaDataCollector = null;

    public function __construct(MediaDataCollector $mediaDataCollector)
    {
        $this->mediaDataCollector = $mediaDataCollector;
    }

    public function findMediaPathByTimeTable($timetable, $fileName)
    {
        $media = new Media();
        $timetableCategory = new Category($timetable->getId(), CategoryType::NETWORK);
        $networkCategory = new Category($timetable->getLineConfig()->getSeason()->getNetwork()->getexternalId(), CategoryType::NETWORK);
        $seasonCategory = new Category($timetable->getLineConfig()->getSeason()->getId(), CategoryType::LINE);

        $timetableCategory->setParent($networkCategory);
        $networkCategory->setParent($seasonCategory);
        $media->setCategory($timetableCategory);
        $media->setFileName($fileName);

        return ($this->mediaDataCollector->getPathByMedia($media));
    }

    public function saveByTimetable($timetable, $path, $fileName)
    {
        $media = new Media();
        $timetableCategory = new Category($timetable->getId(), CategoryType::NETWORK);
        $networkCategory = new Category($timetable->getLineConfig()->getSeason()->getNetwork()->getexternalId(), CategoryType::NETWORK);
        $seasonCategory = new Category($timetable->getLineConfig()->getSeason()->getId(), CategoryType::LINE);

        $timetableCategory->setParent($networkCategory);
        $networkCategory->setParent($seasonCategory);
        $media->setCategory($timetableCategory);
        $media->setFileName($fileName);
        $media->setFile(new File($path));
        $this->mediaDataCollector->save($media);

        return ($media);
    }

    public function copy(Block $origBlock, Block $destBlock, $destTimetable)
    {
        $origImgMediaPath = $this->findMediaPathByTimeTable($origBlock->getTimetable(), ImgHandler::ID_LINE_MAP);
        copy($origImgMediaPath, $origImgMediaPath . '.bak');
        $destMedia = $this->saveByTimetable($destTimetable, $origImgMediaPath, ImgHandler::ID_LINE_MAP);
        $destBlock->setContent($this->mediaDataCollector->getUrlByMedia($destMedia));
        rename($origImgMediaPath . '.bak', $origImgMediaPath);
    }
}
