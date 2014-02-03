<?php

namespace CanalTP\MethBundle\Entity;

use Doctrine\ORM\EntityRepository;
use CanalTP\MethBundle\Entity\StopPoint;

/**
 * BlockRepository
 *
 */
class BlockRepository extends EntityRepository
{
    /**
     * find a block by Line Id And DomId
     *
     * @param  string $lineId Line Id in meth db
     * @param  string $domId  Dom Id in layout
     * @return Block  Entity or null
     */
    public function findByLineAndDomId($lineId, $domId)
    {
        return $this->findOneBy(array('line' => $lineId, 'domId' => $domId));
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
