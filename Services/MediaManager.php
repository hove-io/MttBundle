<?php

namespace CanalTP\MttBundle\Services;

use Symfony\Component\HttpFoundation\File\File;

use CanalTP\MediaManager\Category\CategoryType;
use CanalTP\MediaManagerBundle\DataCollector\MediaDataCollector;
use CanalTP\MediaManagerBundle\Entity\Category;
use CanalTP\MediaManagerBundle\Entity\Media;

use CanalTP\MttBundle\Entity\Block;

class MediaManager
{
    private $mediaDataCollector = null;
    const TIMETABLE_FILENAME = 'timetable';

    public function __construct(MediaDataCollector $mediaDataCollector)
    {
        $this->mediaDataCollector = $mediaDataCollector;
    }

    public function getSeasonCategory($networkCategoryValue, $routeCategoryValue, $seasonCategoryValue, $externalStopPointId)
    {
        $networkCategory = new Category(
            $networkCategoryValue,
            CategoryType::NETWORK
        );
        $networkCategory->setRessourceId('networks');
        $routeCategory = new Category(
            $routeCategoryValue,
            CategoryType::LINE
        );
        $routeCategory->setRessourceId('routes');
        $seasonCategory = new Category(
            $seasonCategoryValue,
            CategoryType::LINE
        );
        $seasonCategory->setRessourceId('seasons');

        $routeCategory->setParent($networkCategory);
        if ($externalStopPointId) {
            $stopPointCategory = new Category(
                $externalStopPointId,
                CategoryType::LINE
            );
            $stopPointCategory->setParent($routeCategory);
            $stopPointCategory->setRessourceId('stop_points');
            $seasonCategory->setParent($stopPointCategory);
        } else {
            $seasonCategory->setParent($routeCategory);
        }
        
        return $seasonCategory;
    }
    
    // prepare media regarding Mtt policy
    private function getMedia($timetable, $externalStopPointId = false)
    {
        $seasonCategory = $this->getSeasonCategory(
            $timetable->getLineConfig()->getSeason()->getNetwork()->getexternalId(),
            $timetable->getExternalRouteId(),
            $timetable->getLineConfig()->getSeason()->getId(),
            $externalStopPointId
        );

        $media = new Media();
        $media->setCategory($seasonCategory);

        return $media;
    }

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

    public function deleteSeasonMedias($season)
    {
        $seasonCategory = $this->getSeasonCategory($season->getNetwork()->getexternalId(), '*', $season->getId(), '*');
        // $seasonCategory->delete();
    }

    public function copy(Block $origBlock, Block $destBlock, $destTimetable)
    {
        $origImgMediaPath = $this->findMediaPathByTimeTable($origBlock->getTimetable(), $origBlock->getDomId());
        if (!empty($origImgMediaPath)) {
            copy($origImgMediaPath, $origImgMediaPath . '.bak');
            $destMedia = $this->saveByTimetable($destTimetable, new File($origImgMediaPath), $origBlock->getDomId());
            $destBlock->setContent($this->mediaDataCollector->getUrlByMedia($destMedia));
            // no rename because of the NFS bug
            copy($origImgMediaPath . '.bak', $origImgMediaPath);
            unlink($origImgMediaPath . '.bak');
        }
    }
}
