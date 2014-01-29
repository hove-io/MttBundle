<?php

namespace CanalTP\MethBundle\Form\Handler\Block;

use Doctrine\Common\Persistence\ObjectManager;
use CanalTP\MethBundle\Entity\Block;

class TextHandler
{
    private $om = null;
    private $block = null;

    public function __construct(ObjectManager $om, Block $block)
    {
        $this->om = $om;
        $this->block = $block;
    }

    public function process($data, $line_id)
    {
        if (empty($this->block)) {
            $this->block = new Block();
            // get partialreference to avoid SQL statement
            $line = $this->om->getPartialReference('CanalTP\MethBundle\Entity\Line', $line_id);
            $this->block->setLine($line);
            $this->block->setContent($data['content']);
            $this->block->setTitle($data['title']);
            $this->block->setDomId($data['dom_id']);
            $this->block->setTypeId($data['type_id']);
        }
        $this->om->persist($this->block);
        $this->om->flush();
    }
}
