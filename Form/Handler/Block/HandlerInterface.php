<?php

namespace CanalTP\MttBundle\Form\Handler\Block;

use CanalTP\MethBundle\Entity\Block;

interface HandlerInterface
{
    public function process(Block $formBlock, $lineId);
}
