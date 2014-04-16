<?php

namespace CanalTP\MttBundle\Entity;

use Doctrine\ORM\EntityRepository;

/**
 * BlockRepository
 *
 */
class BlockRepository extends EntityRepository
{
    const TEXT_TYPE      = 'text';
    const IMG_TYPE       = 'img';
    const CALENDAR_TYPE  = 'calendar';

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
            $timetable = $this->getEntityManager()->getRepository('CanalTPMttBundle:Timetable')->find(
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
    public function findByTimetableAndStopPointAndDomId($timetableId, $externalStopPointId, $domId)
    {
        $query = $this->getEntityManager()
            ->createQuery(
                'SELECT block FROM CanalTPMttBundle:Block block
                INNER JOIN block.stopPoint stop_point
                WHERE stop_point.externalId = :externalStopPointId
                AND block.timetable = :timetable
                AND block.domId = :domId'
            )
            ->setParameter('externalStopPointId', $externalStopPointId)
            ->setParameter('timetable', $timetableId)
            ->setParameter('domId', $domId);

        return $query->getOneOrNullResult();
    }
}
