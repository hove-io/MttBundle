<?php

namespace CanalTP\MttBundle\Entity;

use Doctrine\ORM\EntityRepository;

/**
 * AmqpTaskRepository
 *
 */
class AmqpTaskRepository extends EntityRepository
{
    public function getLastPerimeterTasks($perimeter, $limit = 5)
    {
        $qb = $this->createQueryBuilder("amqpTask")
            ->select("amqpTask")
            ->where("amqpTask.perimeter = :perimeter")
            ->setParameter("perimeter", $perimeter)
            ->add('orderBy', 'amqpTask.created DESC')
            ->setMaxResults($limit);

        return $qb->getQuery()->getResult();
    }

    public function findTasksByObjectIds($objectIds = array())
    {
        $q = $this->createQueryBuilder('v')
            ->select('v')
            ->andWhere('v.objectId IN (:objectIds)')
            ->setParameter('objectIds', $objectIds)
            ->getQuery();

        return $q->getResult();
    }

    public function findTasksOlderThan($datetime)
    {
        $q = $this->createQueryBuilder('v')
            ->select('v')
            ->andWhere('v.created < (:datetime)')
            ->andWhere('v.status != (:status)')
            ->setParameter('datetime', $datetime)
            ->setParameter('status', AmqpTask::LAUNCHED_STATUS)
            ->getQuery();

        return $q->getResult();
    }
}
