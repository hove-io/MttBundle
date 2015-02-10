<?php

namespace CanalTP\MttBundle\Services;

use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpFoundation\File\File;

use CanalTP\MediaManager\Category\CategoryType;
use CanalTP\MediaManagerBundle\DataCollector\MediaDataCollector;
use CanalTP\MediaManagerBundle\Entity\Category;
use CanalTP\MediaManagerBundle\Entity\Media;

use CanalTP\MttBundle\Entity\Block;
use CanalTP\MttBundle\Entity\Timetable;
use CanalTP\MttBundle\Entity\LineTimecard;
use Symfony\Component\Validator\Constraints\Time;


class MediaManager
{
    private $mediaDataCollector = null;
    const TIMETABLE_FILENAME = 'timetable';
    const LINETIMECARD_FILENAME = 'linetimecard';

    public function __construct(MediaDataCollector $mediaDataCollector)
    {
        $this->mediaDataCollector = $mediaDataCollector;
    }

    public function getSeasonCategory($networkCategoryValue, $routeCategoryValue, $seasonCategoryValue, $externalStopPointId = false)
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

    public function getSeasonCategoryForLine($networkCategoryValue, $lineCategoryValue, $seasonCategoryValue)
    {
        $networkCategory = new Category(
            $networkCategoryValue,
            CategoryType::NETWORK
        );
        $networkCategory->setRessourceId('networks');
        $lineCategory = new Category(
            $lineCategoryValue,
            CategoryType::LINE
        );

        $lineCategory->setRessourceId('lines');
        $seasonCategory = new Category(
            $seasonCategoryValue,
            CategoryType::LINE
        );

        $seasonCategory->setRessourceId('seasons');

        $lineCategory->setParent($networkCategory);
        $seasonCategory->setParent($lineCategory);


        return $seasonCategory;
    }

    // prepare media regarding Mtt policy
    private function getMedia($object, $externalStopPointId = false)
    {
        $externalNetworkId = $object->getLineConfig()->getSeason()->getPerimeter()->getExternalNetworkId();
        $seasonId = $object->getLineConfig()->getSeason()->getId();

        switch($object->__toString()) {
            case lineTimecard::OBJECT_TYPE:
                $seasonCategory = $this->getSeasonCategoryForLine(
                    $externalNetworkId,
                    $object->getLineId(),
                    $seasonId
                );
                break;
            case Timetable::OBJECT_TYPE:
                $seasonCategory = $this->getSeasonCategory(
                    $externalNetworkId,
                    $object->getExternalRouteId(),
                    $seasonId,
                    $externalStopPointId
                );
                break;
            default:
                throw new Exception('Object ' . $object . ' not supported');
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

    public function getStopPointTimetableMedia($timetable, $externalStopPointId)
    {
        $media = $this->getMedia($timetable, $externalStopPointId);
        $media->setFileName(self::TIMETABLE_FILENAME);

        return $media;
    }



    public function getLineTimecardMedia($lineTimecard)
    {
        $media = $this->getMedia($lineTimecard);
        $media->setFileName(self::LINETIMECARD_FILENAME);

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

    public function savePdf($object, $path, $externalStopPointId = null)
    {
        if ($object instanceof LineTimecard) {
            $media = $this->getLineTimecardMedia($object);
        } else if ($object instanceof Timetable) {
            $media = $this->getStopPointTimetableMedia($object, $externalStopPointId);
        } else {
            throw new Exception('Object ' . $object . ' is not suported' );
        }

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

    public function saveByObject($object, $file, $fileName)
    {
        $media = $this->getMedia($object);
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
