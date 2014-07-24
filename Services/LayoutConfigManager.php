<?php

namespace CanalTP\MttBundle\Services;

use Doctrine\Common\Persistence\ObjectManager;
use CanalTP\MttBundle\Entity\LayoutConfig;

class LayoutConfigManager
{
    private $om = null;
    private $repository = null;

    public function __construct(ObjectManager $om)
    {
        $this->om = $om;
        $this->repository = $this->om->getRepository('CanalTPMttBundle:LayoutConfig');
    }

    public function findAll()
    {
        return ($this->repository->findAll());
    }

    public function save($layoutConfig)
    {
        //TODO: Add NotesMode field in LayoutConfigType. (Create custom Layout)
        $layoutConfig->setNotesMode(LayoutConfig::NOTES_MODE_DISPATCHED);
        $this->om->persist($layoutConfig);
        $this->om->flush();
    }
}
