<?php

namespace CanalTP\MethBundle\Entity;

use Doctrine\ORM\EntityRepository;
use CanalTP\MethBundle\Entity\Block;
use CanalTP\MethBundle\Entity\StopPoint;

/**
 * BlockRepository
 *
 */
class BlockRepository extends EntityRepository
{
    /**
     * find a block by Route Id And DomId
     *
     * @param  string $lineId Line Id in meth db
     * @param  string $domId  Dom Id in layout
     * @return Block  Entity or null
     */
    public function findByRouteAndDomId($routeId, $domId)
    {
        $query = $this->getEntityManager()
            ->createQuery('
                SELECT block FROM CanalTPMethBundle:Block block
                INNER JOIN block.route route
                WHERE route.externalId = :externalId AND block.domId = :domId')
            ->setParameter('domId', $domId)
            ->setParameter('externalId', $routeId);
        $block = $query->getOneOrNullResult();
        // no route inserted yet so create a non persistent block
        if (empty($block))
        {
            $block = new Block();
            $block->setDomId($domId);
        }
        return $block;
    }

    /**
     * find a block By StopPoint Navitia Id And DomId
     *
     * @param  string $navitiaId Stop point navitia Id
     * @param  string $domId     Dom Id in layout
     * @return Block  Entity or null
     */
    public function findByStopPointAndDomId($navitiaId, $domId)
    {
        $query = $this->getEntityManager()
            ->createQuery('
                SELECT block FROM CanalTPMethBundle:Block block
                INNER JOIN block.stopPoint stop_point
                WHERE stop_point.navitiaId = :navitiaId AND block.domId = :domId')
            ->setParameter('domId', $domId)
            ->setParameter('navitiaId', $navitiaId);

        return $query->getOneOrNullResult();
    }
}
