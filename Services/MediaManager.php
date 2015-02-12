<?php

namespace CanalTP\MttBundle\Services;

use Symfony\Component\HttpFoundation\File\File;

use CanalTP\MediaManagerBundle\DataCollector\MediaDataCollector;
use CanalTP\MediaManagerBundle\Entity\Media;
use CanalTP\MttBundle\MediaManager\Category\CategoryType;
use CanalTP\MttBundle\MediaManager\Category\Factory\CategoryFactory;

use CanalTP\MttBundle\Entity\Block;

class MediaManager
{
    private $mediaDataCollector = null;
    private $categoryFactory = null;
    const TIMETABLE_FILENAME = 'timetable';

    public function __construct(MediaDataCollector $mediaDataCollector)
    {
        $this->mediaDataCollector = $mediaDataCollector;
        $this->categoryFactory = new CategoryFactory();
    }

    public function getSeasonCategory($networkCategoryValue, $routeCategoryValue, $seasonCategoryValue, $externalStopPointId = false)
    {
        $networkCategory = $this->categoryFactory->create(CategoryType::NETWORK);
        $networkCategory->setId($networkCategoryValue);
        $routeCategory = $this->categoryFactory->create(CategoryType::ROUTE);
        $routeCategory->setId($routeCategoryValue);
        $seasonCategory = $this->categoryFactory->create(CategoryType::SEASON);
        $seasonCategory->setId($seasonCategoryValue);

        $routeCategory->setParent($networkCategory);
        if ($externalStopPointId) {
            $stopPointCategory = $this->categoryFactory->create(CategoryType::STOP_POINT);
            $stopPointCategory->setId($externalStopPointId);
            $stopPointCategory->setParent($routeCategory);
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
            $timetable->getLineConfig()->getSeason()->getPerimeter()->getExternalNetworkId(),
            $timetable->getExternalRouteId(),
            $timetable->getLineConfig()->getSeason()->getId(),
            $externalStopPointId
        );

        $media = new Media();
        $media->setCategory($seasonCategory);
        $media->setCompany($this->mediaDataCollector->getCompany());

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
        $seasonCategory = $this->getSeasonCategory($season->getPerimeter()->getExternalNetworkId(), '*', $season->getId(), '*');
        $seasonCategory->delete($this->mediaDataCollector->getCompany(), true);
        $seasonCategory = $this->getSeasonCategory($season->getPerimeter()->getExternalNetworkId(), '*', $season->getId());
        $seasonCategory->delete($this->mediaDataCollector->getCompany(), true);
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
