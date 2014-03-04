<?php

namespace CanalTP\MttBundle\Form\Handler\Block;

use CanalTP\MttBundle\Entity\Block;
use CanalTP\MttBundle\Entity\StopPoint;

abstract class AbstractHandler implements HandlerInterface
{
    private $stopPoint = null;
    protected $om = null;
    protected $block = null;

    protected function getRouteByExternalId($routeExternalId)
    {
        $route = $this->om
            ->getRepository('CanalTPMttBundle:Route')
            ->findOneByExternalId($routeExternalId);

        return ($route);
    }

    protected function saveBlock(Block $formBlock, $routeExternalId)
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
        $this->initRelation($formBlock, $routeExternalId);
        $this->om->persist($this->block);

        $this->om->flush();
    }

    private function getStopPointReference($externalStopPointId)
    {
        $this->stopPoint = $this->om
            ->getRepository('CanalTPMttBundle:StopPoint')
            ->getStopPoint($externalStopPointId);

        return ($this->om->getPartialReference(
            'CanalTP\MttBundle\Entity\StopPoint',
            $this->stopPoint->getId()
        ));
    }

    protected function initRelation(Block $block, $timetable)
    {
        $externalStopPointId = $block->getStopPoint();

        // shall we link this block to a specific stop point and/or a timetable?
        if (empty($externalStopPointId)) {
            $this->block->setTimetable($timetable);
        } else {
            // link block to this stop point
            $this->block->setStopPoint(
                $this->getStopPointReference(
                    $externalStopPointId
                )
            );
        }
    }
}
