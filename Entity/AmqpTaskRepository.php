<?php

namespace CanalTP\MttBundle\Entity;

use Doctrine\ORM\EntityRepository;

/**
 * AmqpTaskRepository
 *
 */
class AmqpTaskRepository extends EntityRepository
{
    public function getLastNetworkTasks($network, $limit = 5)
    {
        $qb = $this->createQueryBuilder("amqpTask")
            ->select("amqpTask")
            ->join("amqpTask.network", "network")
            ->where("network.id = :networkId")
            ->setParameter("networkId", $network->getId())
            ->add('orderBy', 'amqpTask.created DESC')
            ->setMaxResults($limit);
        return $qb->getQuery()->getResult();
    }
    
    public function findTasksByObjectIds($objectIds = array()) {
        $q = $this->createQueryBuilder('v')
            ->select('v')
            ->andWhere('v.objectId IN (:objectIds)')
            ->setParameter('objectIds', $objectIds)
            ->getQuery();

        return $q->getResult();
    }
    
    public function findTasksOlderThan($datetime) {
        $q = $this->createQueryBuilder('v')
            ->select('v')
            ->andWhere('v.created < (:datetime)')
            ->setParameter('datetime', $datetime)
            ->getQuery();

        return $q->getResult();
    }
}