<?php

namespace CanalTP\MttBundle\Services;

use Symfony\Component\HttpFoundation\File\File;

use CanalTP\MediaManagerBundle\DataCollector\MediaDataCollector;
use CanalTP\MediaManagerBundle\Entity\Media;
use CanalTP\MttBundle\MediaManager\Category\CategoryType;
use CanalTP\MttBundle\MediaManager\Category\Factory\CategoryFactory;

use CanalTP\MttBundle\Entity\Block;
use CanalTP\MttBundle\Entity\StopTimetable;
use CanalTP\MttBundle\Entity\LineTimetable;

class MediaManager
{
    private $mediaDataCollector = null;
    private $categoryFactory = null;
    const TIMETABLE_FILENAME = '_timetable';

    public function __construct(MediaDataCollector $mediaDataCollector)
    {
        $this->mediaDataCollector = $mediaDataCollector;
        $this->categoryFactory = new CategoryFactory();
    }

    public function getStopTimetableSeasonCategory(
        $networkCategoryValue,
        $routeCategoryValue,
        $seasonCategoryValue,
        $externalStopPointId = false
    ) {
        $networkCategory = $this->categoryFactory->create(CategoryType::NETWORK);
        $networkCategory->setId($networkCategoryValue);
        $seasonCategory = $this->categoryFactory->create(CategoryType::SEASON);
        $seasonCategory->setId($seasonCategoryValue);
        $routeCategory = $this->categoryFactory->create(CategoryType::ROUTE);
        $routeCategory->setId($routeCategoryValue);
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

    public function getLineTimetableSeasonCategory(
        $networkCategoryValue,
        $seasonCategoryValue,
        $lineCategoryValue
    ) {
        $networkCategory = $this->categoryFactory->create(CategoryType::NETWORK);
        $networkCategory->setId($networkCategoryValue);
        $seasonCategory = $this->categoryFactory->create(CategoryType::SEASON);
        $seasonCategory->setId($seasonCategoryValue);
        $lineCategory = $this->categoryFactory->create(CategoryType::LINE);
        $lineCategory->setId($lineCategoryValue);

        $lineCategory->setParent($networkCategory);
        $seasonCategory->setParent($lineCategory);

        return $seasonCategory;
    }

    // prepare media regarding Mtt policy
    private function getMedia($timetable, $externalStopPointId = false)
    {
        if ($timetable instanceof StopTimetable) {
            $seasonCategory = $this->getStopTimetableSeasonCategory(
                $timetable->getLineConfig()->getSeason()->getPerimeter()->getExternalNetworkId(),
                $timetable->getExternalRouteId(),
                $timetable->getLineConfig()->getSeason()->getId(),
                $externalStopPointId
            );
        } elseif ($timetable instanceof LineTimetable) {
            $seasonCategory = $this->getLineTimetableSeasonCategory(
                $timetable->getLineConfig()->getSeason()->getPerimeter()->getExternalNetworkId(),
                $timetable->getLineConfig()->getExternalLineId(),
                $timetable->getLineConfig()->getSeason()->getId()
            );
        } else {
            throw new \Exception('Bad Timetable object');
        }

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
        $media->setFileName('stop'.self::TIMETABLE_FILENAME);

        return $media;
    }

    public function saveStopPointStopTimetable($stopTimetable, $externalStopPointId, $path)
    {
        $media = $this->getStopPointStopTimetableMedia($stopTimetable, $externalStopPointId);
        $media->setFile(new File($path));
        $this->mediaDataCollector->save($media);

        return ($media);
    }

    public function findMediaPathByTimeTable($timetable, $fileName)
    {
        $media = $this->getMedia($timetable);
        $media->setFileName($fileName);

        return ($this->getPathByMedia($media));
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
