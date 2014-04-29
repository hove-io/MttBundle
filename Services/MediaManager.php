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
    const TIMETABLE_FILENAME = 'timetable';

    public function __construct(MediaDataCollector $mediaDataCollector)
    {
        $this->mediaDataCollector = $mediaDataCollector;
    }

    // prepare media regarding Mtt policy
    private function getMedia($timetable, $externalStopPointId = false)
    {
        $networkCategory = new Category(
            $timetable->getLineConfig()->getSeason()->getNetwork()->getexternalId(),
            CategoryType::NETWORK
        );
        $routeCategory = new Category(
            $timetable->getExternalRouteId(),
            CategoryType::LINE
        );
        $seasonCategory = new Category(
            $timetable->getLineConfig()->getSeason()->getId(),
            CategoryType::LINE
        );

        $routeCategory->setParent($networkCategory);
        if ($externalStopPointId) {
            $stopPointCategory = new Category(
                $externalStopPointId,
                CategoryType::LINE
            );
            $stopPointCategory->setParent($routeCategory);
            $seasonCategory->setParent($stopPointCategory);
        } else {
            $seasonCategory->setParent($routeCategory);
        }
        
        $media = new Media();
        $media->setCategory($seasonCategory);

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
        $media = $this->getMedia($timetable, $externalStopPointId);
        $media->setFileName(self::TIMETABLE_FILENAME);

        return $media;
    }
    
    public function findMediaPathByTimeTable($timetable, $fileName)
    {
        $media = $this->getMedia($timetable);
        $media->setFileName($fileName);

        return ($this->getPathByMedia($media));
    }

    public function saveStopPointTimetable($timetable, $externalStopPointId, $path)
    {
        $media = $this->getStopPointTimetableMedia($timetable, $externalStopPointId);
        $media->setFile(new File($path));
        $this->mediaDataCollector->save($media);

        return ($media);
    }
    
    public function saveByTimetable($timetable, $file, $fileName)
    {
        $media = $this->getMedia($timetable);
        $media->setFileName($fileName);
        $media->setFile($file);
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
        $destMedia = $this->saveByTimetable($destTimetable, new File($origImgMediaPath), ImgHandler::ID_LINE_MAP);
        $destBlock->setContent($this->mediaDataCollector->getUrlByMedia($destMedia));
        rename($origImgMediaPath . '.bak', $origImgMediaPath);
    }
}
