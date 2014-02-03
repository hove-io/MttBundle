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

    protected function saveBlock(Block $newBlock, $lineId)
    {
        $this->block = new Block();
        $line = $this->om->getPartialReference(
            'CanalTP\MethBundle\Entity\Line',
            $lineId
        );

        $this->block->setLine($line);
        $this->block->setTitle($newBlock->getTitle());
        $this->block->setContent($newBlock->getContent());
        $this->block->setTypeId($newBlock->getTypeId());
        $this->block->setDomId($newBlock->getDomId());
        $this->om->persist($this->block);
    }

    private function saveStopPoint($lineId)
    {
        $this->stopPoint = new StopPoint();
        $line = $this->om->getPartialReference(
            'CanalTP\MethBundle\Entity\Line',
            $lineId
        );

        $this->stopPoint->setNavitiaId($stopPointId);
        $this->stopPoint->setLine($line);
        $this->om->persist($this->stopPoint);
    }

    private function getStopPointReference()
    {
        $this->stopPoint = $this->om
            ->getRepository('CanalTPMethBundle:StopPoint', 'meth')
            ->findOneByNavitiaId($stopPointId);
        // do this stop_point exists?
        if (empty($this->stopPoint)) {
            $this->saveStopPoint($lineId);
        }

        return ($this->om->getPartialReference(
            'CanalTP\MethBundle\Entity\StopPoint',
            $this->stopPoint->getId()
        ));
    }

    protected function initRelation(Block $block, $lineId)
    {
         $stopPointId = $block->getStopPoint();

        // should we link this block to a specific stop point?
         if (!empty($stopPointId)) {
            // link block to this stop point
            $this->block->setStopPoint($this->getStopPointReference());
        } else {
            // get partialreference to avoid SQL statement
            $line = $this->om->getPartialReference(
                'CanalTP\MethBundle\Entity\Line',
                $lineId
            );
            $this->block->setLine($line);
        }
    }
}
