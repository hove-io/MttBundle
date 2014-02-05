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
        $this->block = new Block();
        $this->initRelation($formBlock, $lineId);

        $this->block->setTitle($formBlock->getTitle());
        $this->block->setContent($formBlock->getContent());
        $this->block->setTypeId($formBlock->getTypeId());
        $this->block->setDomId($formBlock->getDomId());
        $this->om->persist($this->block);
    }

    private function saveStopPoint($lineId, $stopPointId)
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

    private function getStopPointReference($lineId, $stopPointId)
    {
        $this->stopPoint = $this->om
            ->getRepository('CanalTPMethBundle:StopPoint', 'meth')
            ->findOneByNavitiaId($stopPointId);
        // do this stop_point exists?
        if (empty($this->stopPoint)) {
            $this->saveStopPoint($lineId, $stopPointId);
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
         if (empty($stopPointId)) {
            // get partialreference to avoid SQL statement
            $line = $this->om->getPartialReference(
                'CanalTP\MethBundle\Entity\Line',
                $lineId
            );
            $this->block->setLine($line);
        } else {

            // link block to this stop point
            $this->block->setStopPoint($this->getStopPointReference(
                $lineId,
                $stopPointId
            ));
        }
    }
}
