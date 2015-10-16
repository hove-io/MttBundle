<?php

namespace CanalTP\MttBundle\Form\Handler\Block;

use CanalTP\MttBundle\Entity\Block;
use CanalTP\MttBundle\Entity\StopPoint;

abstract class AbstractHandler implements HandlerInterface
{
    private $stopPoint = null;
    protected $om = null;
    protected $block = null;

    protected function saveBlock(Block $formBlock, $stopTimetable)
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
        $this->initRelation($formBlock, $stopTimetable);
        $this->om->persist($this->block);

        $this->om->flush();
    }

    private function getStopPointReference($externalStopPointId, $stopTimetable)
    {
        $this->stopPoint = $this->om
            ->getRepository('CanalTPMttBundle:StopPoint')
            ->getStopPoint(
                $externalStopPointId,
                $stopTimetable
            );

        return $this->stopPoint;
    }

    protected function initRelation(Block $block, $stopTimetable)
    {
        $externalStopPointId = $block->getStopPoint();

        // all blocks are linked at least to a stopTimetable
        $this->block->setStopTimetable($stopTimetable);
        if (!empty($externalStopPointId)) {
            // link block to this stop point
            $this->block->setStopPoint(
                $this->getStopPointReference(
                    $externalStopPointId,
                    $stopTimetable
                )
            );
        }
    }
}
