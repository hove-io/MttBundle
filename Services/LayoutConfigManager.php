<?php

namespace CanalTP\MttBundle\Services;

use Doctrine\Common\Persistence\ObjectManager;

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
        $this->om->persist($layoutConfig);
        $this->om->flush();
    }
}
