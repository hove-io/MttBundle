<?php

namespace CanalTP\MethBundle\Form\Handler\Block;

use Doctrine\Common\Persistence\ObjectManager;

class textHandler
{
    private $om = null;

    public function __construct(ObjectManager $om)
    {
        $this->om = $om;
    }

    public function process($data)
    {
        return false;
    }

    protected function onSuccess(GroupInterface $group)
    {
        $this->groupManager->updateGroup($group);
    }
}
