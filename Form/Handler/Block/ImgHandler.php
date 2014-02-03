<?php

namespace CanalTP\MethBundle\Form\Handler\Block;

use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\Filesystem\Filesystem;
use Doctrine\Common\Persistence\ObjectManager;
use CanalTP\MethBundle\Form\Handler\Block\AbstractHandler;
use CanalTP\MethBundle\Entity\Block;

class ImgHandler extends AbstractHandler
{
    private $co = null;
    private $lastImgPath = null;

    public function __construct(Container $co, ObjectManager $om, $block, $lastImgPath)
    {
        $this->co = $co;
        $this->om = $om;
        $this->block = $block;
        $this->lastImgPath = $lastImgPath;
    }

    // Remove previous file. Pb was: block->content already has new value
    private function removeOldImg(Filesystem $fs, $destDir)
    {
        $oldPath = $destDir . $this->lastImgPath;

        if ($fs->exists($oldPath)) {
            $fs->remove($oldPath);
        }
    }

    public function process(Block $block, $lineId)
    {
        $fs = new Filesystem();
        $relativePath = '/uploads' . '/line-' . $lineId . '/';
        $filename = $block->getDomId() . '-' . $block->getContent()->getClientOriginalName();
        $destDir = realpath($this->co->get('kernel')->getRootDir() . '/../web');
        $new_media = $block->getContent()->move($destDir . $relativePath, $filename);

        if (!empty($this->lastImgPath) && $this->lastImgPath != $filename) {
            $this->removeOldImg($fs, $destDir);
        }
        if ($fs->exists($new_media->getRealPath())) {
            $block->setContent($relativePath . $filename);
        }
        if (empty($this->block)) {
            $this->saveBlock($block, $lineId);
        }
        $this->om->flush();
    }
}
