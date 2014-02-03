<?php

namespace CanalTP\MethBundle\Form\Handler\Block;

use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Exception\IOException;
use Doctrine\Common\Persistence\ObjectManager;
use CanalTP\MethBundle\Form\Handler\Block\AbstractHandler;
use CanalTP\MethBundle\Entity\Block;

class ImgHandler extends AbstractHandler
{
    private $co = null;
    private $block = null;
    private $previousData = null;

    public function __construct(Container $co, ObjectManager $om, $block, $previousData)
    {
        $this->co = $co;
        $this->om = $om;
        $this->block = $block;
        $this->previousData = $previousData;
    }

    public function process(Block $block, $lineId)
    {
        $fs = new Filesystem();
        $relativePath = '/uploads' . '/line-' . $lineId . '/';
        $filename = $block->getDomId() . '-' . $block->getContent()->getClientOriginalName();
        $destDir = realpath($this->co->get('kernel')->getRootDir() . '/../web');        
        $new_media = $block->getContent()->move($destDir . $relativePath, $filename);

        if (empty($this->block))
        {
            $this->block = new Block();
            // get partialreference to avoid SQL statement
            $line = $this->om->getPartialReference('CanalTP\MethBundle\Entity\Line', $lineId);
            $this->block->setLine($line);
            $this->block->setTitle($block->getTitle());
            $this->block->setDomId($block->getDomId());
            $this->block->setTypeId($block->getTypeId());
        }
        // remove previous file. Pb was: block->content already has new value
        else if (!empty($this->previousData))
        {
            $oldPath = $destDir . $this->previousData['content'];

            if ($fs->exists($oldPath))
            {
                $fs->remove($oldPath);
            }
        }
        if ($fs->exists($new_media->getRealPath()))
        {
            $this->block->setContent($relativePath . $filename);
        }
        $this->om->persist($this->block);
        $this->om->flush();
    }
}
