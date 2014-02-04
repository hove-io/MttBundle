<?php

namespace CanalTP\MethBundle\Form\Handler\Block;

use Doctrine\Common\Persistence\ObjectManager;
use CanalTP\MethBundle\Form\Handler\Block\AbstractHandler;
use CanalTP\MethBundle\Entity\Block;

class TextHandler extends AbstractHandler
{
    public function __construct(ObjectManager $om, $block)
    {
        $this->om = $om;
        $this->block = $block;
    }

    public function process(Block $formBlock, $lineId)
    {
        if (empty($this->block)) {
            $this->saveBlock($formBlock, $lineId);
        }
        $this->om->flush();
    }
}
