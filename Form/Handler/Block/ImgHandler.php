<?php

namespace CanalTP\MethBundle\Form\Handler\Block;

use Doctrine\Common\Persistence\ObjectManager;
use CanalTP\MethBundle\Entity\Block;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Exception\IOException;

class ImgHandler
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

    public function process($data, $lineId)
    {
        $fs = new Filesystem();
        $relativePath = '/uploads' . '/line-' . $lineId . '/';
        $filename = $data->getDomId() . '-' . $data->getContent()->getClientOriginalName();
        $destDir = realpath($this->co->get('kernel')->getRootDir() . '/../web');        
        $new_media = $data->getContent()->move($destDir . $relativePath, $filename);

        if (empty($this->block))
        {
            $this->block = new Block();
            // get partialreference to avoid SQL statement
            $line = $this->om->getPartialReference('CanalTP\MethBundle\Entity\Line', $lineId);
            $this->block->setLine($line);
            $this->block->setTitle($data->getTitle());
            $this->block->setDomId($data->getDomId());
            $this->block->setTypeId($data->getTypeId());
        }
        // remove previous file. Pb was: block->content already has new value
        else if (!empty($this->previousData))
        {
            $oldPath = $destDir . $this->previousData['content'];
            // var_dump($this->previousData, $oldPath);die;
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
