<?php

namespace CanalTP\MethBundle\Entity;

use Doctrine\ORM\EntityRepository;

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
    public function findByTimetableAndDomId($timetableId, $domId)
    {
        $block = $this->findOneBy(
            array(
                'timetable' => $timetableId,
                'domId' => $domId,
            )
        );
        // no block found so create first a non persistent block
        if (empty($block)) {
            $block = new Block();
            $block->setDomId($domId);
            $timetable = $this->getEntityManager()->getPartialReference(
                'CanalTP\MethBundle\Entity\Timetable',
                $timetableId
            );
            $block->setTimetable($timetable);
        }

        return $block;
    }

    /**
     * find a block By StopPoint Navitia Id And DomId
     *
     * @param  string $externalStopPointId Stop point navitia Id
     * @param  string $domId               Dom Id in layout
     * @return Block  Entity or null
     */
    public function findByStopPointAndDomId($externalStopPointId, $domId)
    {
        $query = $this->getEntityManager()
            ->createQuery(
                'SELECT block FROM CanalTPMethBundle:Block block
                INNER JOIN block.stopPoint stop_point
                WHERE stop_point.externalId = :externalId AND block.domId = :domId'
            )
            ->setParameter('domId', $domId)
            ->setParameter('externalId', $externalStopPointId);

        return $query->getOneOrNullResult();
    }
}
