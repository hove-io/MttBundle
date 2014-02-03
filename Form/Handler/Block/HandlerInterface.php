<?php

namespace CanalTP\MethBundle\Form\Handler\Block;

use CanalTP\MethBundle\Entity\Block;

interface HandlerInterface
{
    public function process(Block $block, $line_id);
}