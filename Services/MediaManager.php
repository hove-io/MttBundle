<?php

namespace CanalTP\MttBundle\Services;

use Symfony\Component\HttpFoundation\File\File;

use CanalTP\MediaManager\Category\CategoryType;
use CanalTP\MediaManagerBundle\DataCollector\MediaDataCollector;
use CanalTP\MediaManagerBundle\Entity\Category;
use CanalTP\MediaManagerBundle\Entity\Media;

use CanalTP\MttBundle\Entity\Block;
use CanalTP\MttBundle\Form\Handler\Block\ImgHandler;

class MediaManager
{
    private $mediaDataCollector = null;

    public function __construct(MediaDataCollector $mediaDataCollector)
    {
        $this->mediaDataCollector = $mediaDataCollector;
    }

    // prepare media regarding Mtt policy
    private function getTimetableMedia($timetable)
    {
        $timetableCategory = new Category($timetable->getId(), CategoryType::NETWORK);
        $networkCategory = new Category(
            $timetable->getLineConfig()->getSeason()->getNetwork()->getexternalId(),
            CategoryType::NETWORK
        );
        $seasonCategory = new Category(
            $timetable->getLineConfig()->getSeason()->getId(),
            CategoryType::LINE
        );
        $media = new Media();

        $timetableCategory->setParent($networkCategory);
        $networkCategory->setParent($seasonCategory);
        $media->setCategory($timetableCategory);
        
        return $media;
    }
    
    //proxy calls
    public function getUrlByMedia($media)
    {
        return $this->mediaDataCollector->getUrlByMedia($media);
    }
    
    public function getPathByMedia($media)
    {
        return $this->mediaDataCollector->getPathByMedia($media);
    }
    
    public function getStopPointTimetableMedia($timetable, $externalStopPointId)
    {
        $media = $this->getTimetableMedia($timetable);
        $media->setFileName($externalStopPointId);

        return $media;
    }
    
    public function findMediaPathByTimeTable($timetable, $fileName)
    {
        $media = $this->getTimetableMedia($timetable);
        $media->setFileName($fileName);

        return ($this->getPathByMedia($media));
    }

    public function saveFile($timetable, $filename, $path)
    {
        $media = $this->getTimetableMedia($timetable);
        $media->setFileName($filename);
        $media->setFile(new File($path));

        $this->mediaDataCollector->save($media);

        return ($media);
    }
    
    public function saveByTimetable($timetable, $path, $fileName)
    {
        $media = $this->getTimetableMedia($timetable);
        $media->setFileName($fileName);
        $media->setFile(new File($path));
        $this->mediaDataCollector->save($media);

        return ($media);
    }

    //TODO: Remove. Should be done by the mediaDataCollector AKA Rémy
    public function deleteSeasonMedias($season)
    {
        $configuration = $this->mediaDataCollector->getConfigurations();
        //berk
        $path = $configuration['storage']['path'] . $configuration['name'] . '/' . $season->getId() . '/';

        if (is_dir($path)) {
            //double berk
            shell_exec("rm -rf $path");
        }
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
