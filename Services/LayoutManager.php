<?php

namespace CanalTP\MttBundle\Services;

use Doctrine\Common\Persistence\ObjectManager;

class LayoutManager
{
    private $om = null;
    private $repository = null;

    public function __construct(ObjectManager $om)
    {
        $this->om = $om;
        $this->repository = $this->om->getRepository('CanalTPMttBundle:Layout');
    }

    public function findAll()
    {
        return ($this->repository->findAll());
    }
}
