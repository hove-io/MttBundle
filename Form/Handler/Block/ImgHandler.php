<?php

namespace CanalTP\MethBundle\Form\Handler\Block;

use Doctrine\Common\Persistence\ObjectManager;
use CanalTP\MethBundle\Entity\Block;
use Symfony\Component\DependencyInjection\Container;

class ImgHandler
{
    private $co = null;
    private $block = null;

    public function __construct(Container $co, ObjectManager $om, $block)
    {
        $this->co = $co;
        $this->om = $om;
        $this->block = $block;
    }

    public function process($data, $lineId)
    {
        $relativePath = '/uploads' . '/' . $lineId . '/';
        $filename = $data->getContent()->getBasename() . '.' . $data->getContent()->guessExtension();
        $destDir = realpath($this->co->get('kernel')->getRootDir() . '/../web');        
        $new_media = $data->getContent()->move($destDir . $relativePath, $filename);

        if (empty($this->block)) {
            $this->block = new Block();
            // get partialreference to avoid SQL statement
            $line = $this->om->getPartialReference('CanalTP\MethBundle\Entity\Line', $lineId);
            $this->block->setLine($line);
            $this->block->setTitle($data->getTitle());
            $this->block->setDomId($data->getDomId());
            $this->block->setTypeId($data->getTypeId());
        }
        if (file_exists($new_media->getRealPath()))
        {
            $this->block->setContent($relativePath . $filename);
        }
        $this->om->persist($this->block);
        $this->om->flush();
    }
}
