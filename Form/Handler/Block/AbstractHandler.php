<?php

namespace CanalTP\MethBundle\Form\Handler\Block;

use CanalTP\MethBundle\Form\Handler\Block\HandlerInterface;
use CanalTP\MethBundle\Entity\Block;
use CanalTP\MethBundle\Entity\StopPoint;

abstract class AbstractHandler implements HandlerInterface
{
    private $stopPoint = null;
    protected $om = null;
    protected $block = null;

    protected function getLineById($id)
    {
        $line = $this->om
            ->getRepository('CanalTPMethBundle:Line', 'mtt')
            ->find($id);

        return ($line);
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
    
    private function getStopPointReference($lineId, $stopPointId)
    {
        $this->stopPoint = $this->om
            ->getRepository('CanalTPMethBundle:StopPoint', 'mtt')
            ->getStopPoint($lineId, $stopPointId);
        
        return ($this->om->getPartialReference(
            'CanalTP\MethBundle\Entity\StopPoint',
            $this->stopPoint->getId()
        ));
    }

    private function getRouteReference($routeExternalId)
    {
        $route = $this->om
            ->getRepository('CanalTPMethBundle:Route', 'mtt')
            ->getRoute($routeExternalId);
        
        return ($this->om->getPartialReference(
            'CanalTP\MethBundle\Entity\Route',
            $route->getId()
        ));
    }

    protected function initRelation(Block $block, $routeId)
    {
        $stopPointId = $block->getStopPoint();

        // shall we link this block to a specific stop point or a route?
         if (empty($stopPointId)) {
            $this->block->setRoute($this->getRouteReference($routeId));
        } else {
            // link block to this stop point
            $this->block->setStopPoint($this->getStopPointReference(
                $lineId,
                $stopPointId
            ));
        }
    }
}
