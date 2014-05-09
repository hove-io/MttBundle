<?php

namespace CanalTP\MttBundle\Entity;

use Doctrine\ORM\EntityRepository;

/**
 * AmqpTaskRepository
 *
 */
class AmqpTaskRepository extends EntityRepository
{
    public function getLastNetworkTasks($network, $limit = 3)
    {
        $qb = $this->createQueryBuilder("amqpTask")
            ->select("amqpTask")
            ->join("amqpTask.network", "network")
            ->where("network.id = :networkId")
            ->setParameter("networkId", $network->getId())
            ->setMaxResults($limit);
        return $qb->getQuery()->getResult();
    }
}