<?php

namespace CanalTP\MttBundle\Form\Handler\Block;

use CanalTP\MttBundle\Entity\Block;
use CanalTP\MttBundle\Entity\StopPoint;
use CanalTP\MttBundle\Entity\Timetable;
use CanalTP\MttBundle\Entity\LineTimecard;

abstract class AbstractHandler implements HandlerInterface
{
    private $stopPoint = null;
    protected $om = null;
    protected $block = null;

    protected function saveBlock(Block $formBlock, $object)
    {
        if (empty($this->block)) {
            $this->block = new Block();

            $this->block->setTitle($formBlock->getTitle());
            $this->block->setContent($formBlock->getContent());
            $this->block->setTypeId($formBlock->getTypeId());
            $this->block->setDomId($formBlock->getDomId());
        }
        // we need to init the relations even if the block is already filled with the post values
        // because stop_point_id in post contains the navitiaId value and doctrine expects a bdd ID
        // Plus, init Relations updates modified dates of line entity or stopPoint
        $this->initRelation($formBlock, $object);
        $this->om->persist($this->block);

        $this->om->flush();
    }

    private function getStopPointReference($externalStopPointId, $timetable)
    {
        $this->stopPoint = $this->om
            ->getRepository('CanalTPMttBundle:StopPoint')
            ->getStopPoint(
                $externalStopPointId,
                $timetable
            );

        return $this->stopPoint;
    }

    protected function initRelation(Block $block, $object)
    {
        $isTimetable = ($object instanceof Timetable) ? true : false;
        // TODO preserve setter method name into object $object
        $setter = ($isTimetable) ? 'setTimetable' : 'setLineTimecard';

        $externalStopPointId = $block->getStopPoint();

        // all blocks are linked at least to a timetable
        $this->block->$setter($object);

        if (!empty($externalStopPointId) && $isTimetable) {
            // link block to this stop point
            $this->block->setStopPoint(
                $this->getStopPointReference(
                    $externalStopPointId,
                    $object
                )
            );
        }
    }
}
