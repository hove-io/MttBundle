<?php

namespace CanalTP\MttBundle\Form\Handler\Block;

use CanalTP\MttBundle\Entity\Block;

abstract class AbstractHandler implements HandlerInterface
{
    protected $om = null;
    protected $block = null;

    protected function saveBlock(Block $formBlock, $timetable)
    {
        if (empty($this->block)) {
            $this->block = new Block();

            $this->block->setTitle($formBlock->getTitle());
            $this->block->setContent($formBlock->getContent());
            $this->block->setType($formBlock->getType());
            $this->block->setDomId($formBlock->getDomId());
        }
        $this->block->setStopTimetable($timetable);

        $this->om->persist($this->block);
        $this->om->flush();
    }
}
