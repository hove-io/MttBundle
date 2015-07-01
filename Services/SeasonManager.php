<?php

/**
 * Description of SeasonManager
 *
 * @author rabikhalil
 */

namespace CanalTP\MttBundle\Services;

use Doctrine\Common\Persistence\ObjectManager;
use CanalTP\NmmPortalBundle\Entity\Perimeter;
use CanalTP\MttBundle\Entity\AmqpTask;
use CanalTP\MttBundle\Entity\Season as SeasonEntity;

class SeasonManager
{
    private $repository = null;
    private $om = null;

    public function __construct(ObjectManager $om, AreaPdfManager $areaPdfManager)
    {
        $this->om = $om;
        $this->repository = $om->getRepository('CanalTPMttBundle:Season');
        $this->areaPdfManager = $areaPdfManager;
    }

    public function getSeasonWithPerimeterAndSeasonId(Perimeter $perimeter, $seasonId)
    {
        $season = null;
        if ($seasonId) {
            $season = $this->find($seasonId);
        }

        if (!$season) {
            $season = new SeasonEntity();

            $season->setPerimeter($perimeter);
        }

        return $season;
    }

    public function save($season)
    {
        $this->om->persist($season);
        $this->om->flush();
    }

    public function publish($seasonId)
    {
        $season = $this->find($seasonId);
        $season->setPublished(true);
        $this->save($season);
    }

    public function unpublish($seasonId)
    {
        $season = $this->find($seasonId);
        $season->setPublished(false);
        $this->save($season);
    }

    public function find($seasonId)
    {
        return empty($seasonId) ? false : $this->repository->find($seasonId);
    }

    public function findByPerimeter(Perimeter $perimeter)
    {
        return $this->repository->findByPerimeter($perimeter);
    }

    public function remove($season)
    {
        $taskRepo = $this->om->getRepository('CanalTPMttBundle:AmqpTask');
        // remove season pdf generation tasks
        $tasks = $taskRepo->findBy(
            array(
                'objectId' => $season->getId(),
                'typeId' => AmqpTask::SEASON_PDF_GENERATION_TYPE
            )
        );
        // remove season tasks
        $timetableIds = array();
        foreach ($season->getLineConfigs() as $lineConfig) {
            foreach ($lineConfig->getTimetables() as $timetable) {
                $timetableIds[] = $timetable->getId();
            }
        }
        if (!empty($timetableIds)) {
            $tasks = array_merge($tasks, $taskRepo->findTasksByObjectIds($timetableIds));
        }
        if (count($tasks) > 0) {
            foreach ($tasks as $task) {
                $this->om->remove($task);
            }
        }
        $this->areaPdfManager->removeAreaPdfBySeason($season);
        $this->om->remove($season);
        $this->om->flush();
    }

    public function findSeasonByPerimeterAndDateTime(Perimeter $perimeter, \DateTime $dateTime)
    {
        return $this->repository->findSeasonByPerimeterAndDateTime($perimeter, $dateTime);
    }

    public function getSelected($seasonId, $seasons)
    {
        if ($seasonId == null && count($seasons) > 0) {
            return $seasons[0];
        } else {
            foreach ($seasons as $season) {
                if ($seasonId == $season->getId()) {
                    return $season;
                }
            }
        }
    }
}
