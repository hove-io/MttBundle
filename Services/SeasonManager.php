<?php

/**
 * Description of SeasonManager
 *
 * @author rabikhalil
 */

namespace CanalTP\MttBundle\Services;

use Doctrine\Common\Persistence\ObjectManager;
use CanalTP\MttBundle\Entity\AmqpTask;

class SeasonManager
{
    private $repository = null;
    private $om = null;
    protected $networkManager = null;

    public function __construct(ObjectManager $om, $networkManager)
    {
        $this->om = $om;
        $this->networkManager = $networkManager;
        $this->repository = $om->getRepository('CanalTPMttBundle:Season');
    }

    public function getSeasonWithNetworkIdAndSeasonId($externalNetworkId, $seasonId)
    {
        return ($this->repository->getSeasonByNetworkIdAndSeasonId($externalNetworkId, $seasonId));
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
        // remove distribution list tasks
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
        $this->om->remove($season);
        $this->om->flush();
    }

    public function findSeasonForDateTime(\DateTime $dateTime)
    {
        return $this->repository->findSeasonForDateTime($dateTime);
    }

    public function findAllByExternalNetworkId($externalNetworkId)
    {
        $perimeter = $this->networkManager->getByExternalNetworkId($externalNetworkId);

        return $this->repository->getByPerimeter($perimeter);
    }
    public function findAllByNetworkId($externalNetworkId)
    {
        return $this->findAllByExternalNetworkId($externalNetworkId);
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
