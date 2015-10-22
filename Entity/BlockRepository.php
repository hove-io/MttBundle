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
    public function findByStopTimetableAndDomId($stopTimetableId, $domId)
    {
        $block = $this->findOneBy(
            array(
                'stopTimetable' => $stopTimetableId,
                'domId' => $domId,
            )
        );
        // no block found so create first a non persistent block
        if (empty($block)) {
            $block = new Block();
            $block->setDomId($domId);
            $stopTimetable = $this->getEntityManager()->getRepository('CanalTPMttBundle:StopTimetable')->find(
                $stopTimetableId
            );
            $block->setStopTimetable($stopTimetable);
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
    public function findByStopTimetableAndStopPointAndDomId($stopTimetableId, $externalStopPointId, $domId)
    {
        $query = $this->getEntityManager()
            ->createQuery(
                'SELECT block FROM CanalTPMttBundle:Block block
                INNER JOIN block.stopPoint stop_point
                WHERE stop_point.externalId = :externalStopPointId
                AND block.stopTimetable = :stopTimetable
                AND block.domId = :domId'
            )
            ->setParameter('externalStopPointId', $externalStopPointId)
            ->setParameter('stopTimetable', $stopTimetableId)
            ->setParameter('domId', $domId);

        return $query->getOneOrNullResult();
    }
}
