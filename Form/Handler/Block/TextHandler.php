<?php

namespace CanalTP\MethBundle\Form\Handler\Block;

use Doctrine\Common\Persistence\ObjectManager;
use CanalTP\MethBundle\Form\Handler\Block\AbstractHandler;
use CanalTP\MethBundle\Entity\Block;
use CanalTP\MethBundle\Entity\StopPoint;

class TextHandler extends AbstractHandler
{
    private $om = null;
    private $block = null;

    public function __construct(ObjectManager $om, $block)
    {
        $this->om = $om;
        $this->block = $block;
    }

    public function process(Block $block, $line_id)
    {
        if (empty($this->block))
        {
            $this->block = new Block();
           
            $this->block->setContent($block->getContent());
            $this->block->setTitle($block->getTitle());
            $this->block->setDomId($block->getDomId());
            $this->block->setTypeId($block->getTypeId());
        }
        $this->_checkRelations($block, $line_id);
        $this->om->persist($this->block);
        $this->om->flush();
    }
    
    // TODO put this in parent so other handlers could call it
    private function _checkRelations($block, $stopPointId)
    {
         $line_id = $block->getStopPoint();
        // should we link this block to a specific stop point?
         if (!empty($stopPointId))
        {
            $stopPoint = $this->om
                ->getRepository('CanalTPMethBundle:StopPoint', 'meth')
                ->findOneByNavitiaId($stopPointId);
            // do this stop_point exists?
            if (empty($stopPoint))
            {
                $stopPoint = new StopPoint();
                $stopPoint->setNavitiaId($stopPointId);
                $line = $this->om->getPartialReference('CanalTP\MethBundle\Entity\Line', $line_id);
                $stopPoint->setLine($line);
                $this->om->persist($stopPoint);
            }
            $stopPointReference = $this->om->getPartialReference('CanalTP\MethBundle\Entity\StopPoint', $stopPoint->getId());
            // link block to this stop point
            $this->block->setStopPoint($stopPointReference);
        }
        else
        {
            // get partialreference to avoid SQL statement
            $line = $this->om->getPartialReference('CanalTP\MethBundle\Entity\Line', $line_id);
            $this->block->setLine($line);
        }
    }
}
