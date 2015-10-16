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
    const TIMETABLE_FILENAME = 'stopTimetable';

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
    private function getMedia($stopTimetable, $externalStopPointId = false)
    {
        $seasonCategory = $this->getSeasonCategory(
            $stopTimetable->getLineConfig()->getSeason()->getPerimeter()->getExternalNetworkId(),
            $stopTimetable->getExternalRouteId(),
            $stopTimetable->getLineConfig()->getSeason()->getId(),
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

    public function getStopPointStopTimetableMedia($stopTimetable, $externalStopPointId)
    {
        $media = $this->getMedia($stopTimetable, $externalStopPointId);
        $media->setFileName(self::TIMETABLE_FILENAME);

        return $media;
    }

    public function findMediaPathByTimeTable($stopTimetable, $fileName)
    {
        $media = $this->getMedia($stopTimetable);
        $media->setFileName($fileName);

        return ($this->getPathByMedia($media));
    }

    public function saveStopPointStopTimetable($stopTimetable, $externalStopPointId, $path)
    {
        $media = $this->getStopPointStopTimetableMedia($stopTimetable, $externalStopPointId);
        $media->setFile(new File($path));
        $this->mediaDataCollector->save($media);

        return ($media);
    }

    public function saveByStopTimetable($stopTimetable, $file, $fileName)
    {
        $media = $this->getMedia($stopTimetable);
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

    public function copy(Block $origBlock, Block $destBlock, $destStopTimetable)
    {
        $origImgMediaPath = $this->findMediaPathByTimeTable($origBlock->getStopTimetable(), $origBlock->getDomId());
        if (!empty($origImgMediaPath)) {
            copy($origImgMediaPath, $origImgMediaPath . '.bak');
            $destMedia = $this->saveByStopTimetable($destStopTimetable, new File($origImgMediaPath), $origBlock->getDomId());
            $destBlock->setContent($this->mediaDataCollector->getUrlByMedia($destMedia));
            // no rename because of the NFS bug
            copy($origImgMediaPath . '.bak', $origImgMediaPath);
            unlink($origImgMediaPath . '.bak');
        }
    }
}
