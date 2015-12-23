<?php

namespace CanalTP\MttBundle\Form\Handler\Block;

use Doctrine\Common\Persistence\ObjectManager;
use CanalTP\MttBundle\Entity\Block;

class TextHandler extends AbstractHandler
{
    public function __construct(ObjectManager $om, $block)
    {
        $this->om = $om;
        $this->block = $block;
    }

    public function process(Block $formBlock, $timetable)
    {
        $this->saveBlock($formBlock, $timetable);
    }
}
