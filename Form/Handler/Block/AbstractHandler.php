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
            ->getRepository('CanalTPMethBundle:Line', 'meth')
            ->find($id);

        return ($line);
    }

    protected function saveBlock(Block $formBlock, $lineId)
    {
        if (empty($this->block)) {
            $this->block = new Block();

            $this->block->setTitle($formBlock->getTitle());
            $this->block->setContent($formBlock->getContent());
            $this->block->setTypeId($formBlock->getTypeId());
            $this->block->setDomId($formBlock->getDomId());
            $this->om->persist($this->block);
        }
        // we need to init the relations even if the block is already filled with the post values
        // because stop_point_id in post contains the navitiaId value and doctrine expects a bdd ID
        // Plus, init Relations updates modified dates of line entity or stopPoint
        $this->initRelation($formBlock, $lineId);

        $this->om->flush();
    }
    
    private function getStopPointReference($lineId, $stopPointId)
    {
        $this->stopPoint = $this->om
            ->getRepository('CanalTPMethBundle:StopPoint', 'meth')
            ->getStopPoint($lineId, $stopPointId);
        
        return ($this->om->getPartialReference(
            'CanalTP\MethBundle\Entity\StopPoint',
            $this->stopPoint->getId()
        ));
    }

    protected function initRelation(Block $block, $lineId)
    {
        $stopPointId = $block->getStopPoint();

        // shall we link this block to a specific stop point?
         if (empty($stopPointId)) {
            // get partialreference to avoid SQL statement
            $line = $this->om->getPartialReference(
                'CanalTP\MethBundle\Entity\Line',
                $lineId
            );
            $this->block->setLine($line);
            // update last modified time of the line
            $line = $this->block->getLine();
            $line->setLastModified(new \DateTime());
            $this->om->persist($line);
        } else {
            // link block to this stop point
            $this->block->setStopPoint($this->getStopPointReference(
                $lineId,
                $stopPointId
            ));
            //update last modified time of the stop point
            $this->stopPoint->setLastModified(new \DateTime());
            $this->om->persist($this->stopPoint);
        }
    }
}
