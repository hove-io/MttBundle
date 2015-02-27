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
    const PAGE_BREAK_TYPE   = 'pageBreak';

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
     * @param $lineTimecardId
     * @param $domId
     * @return lock Entity or null
     */
    public function findByLineTimecardIdAndDomId($lineTimecardId, $domId)
    {
        $block = $this->findOneBy(
            array(
                'lineTimecard' => $lineTimecardId,
                'domId' => $domId,
            )
        );

        // no block found so create first a non persistent block
        if (empty($block)) {
            $block = new Block();
            $block->setDomId($domId);
            $lineTimecard = $this->getEntityManager()->getRepository('CanalTPMttBundle:LineTimecard')->find(
                $lineTimecardId
            );
            $block->setLineTimecard($lineTimecard);
        }

        return $block;
    }

    public function findByObjectIdAndDomId($objectId, $objectType, $domId)
    {

        $block = $this->findOneBy(
            array(
                $objectType => $objectId,
                'domId' => $domId,
            )
        );

        // no block found so create first a non persistent block
        if (empty($block)) {
            $block = new Block();
            $block->setDomId($domId);
            $object = $this->getEntityManager()->getRepository('CanalTPMttBundle:'.ucfirst($objectType))->find(
                $objectId
            );
            $setter = 'set' . ucfirst($objectType);
            $block->$setter($object);
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
