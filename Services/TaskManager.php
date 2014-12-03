<?php

namespace CanalTP\MttBundle\Services;

use Doctrine\Common\Persistence\ObjectManager;
use CanalTP\MttBundle\Entity\AmqpTask;

class TaskManager
{
    private $om = null;
    private $repository = null;

    public function __construct(ObjectManager $om)
    {
        $this->om = $om;
        $this->repository = $this->om->getRepository('CanalTPMttBundle:AmqpTask');
    }

    public function remove($id, $type)
    {
        $tasks = $this->repository->findBy(
            array(
                'objectId' => $id,
                'typeId' => $type
            )
        );

        foreach ($tasks as $task) {
            $this->om->remove($task);
        }
    }
}
