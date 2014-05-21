<?php

/**
 * Symfony service to wrap curl calls
 * @author vdegroote
 */
namespace CanalTP\MttBundle\Services\Amqp;

use Doctrine\Common\Persistence\ObjectManager;

class TaskCancelation
{
    public $om = null;
    public $taskRepo = null;

    public function __construct(ObjectManager $om)
    {
        $this->om = $om;
        $this->taskRepo = $this->om->getRepository('CanalTPMttBundle:AmqpTask');
        $this->seasonRepo = $this->om->getRepository('CanalTPMttBundle:Season');
    }


    public function cancel($taskId)
    {
        $task = $this->taskRepo->find($taskId);
        $task->cancel();
        $season = $this->seasonRepo->find($task->getObjectId());
        $season->setLocked(false);
        $this->om->flush();
    }
}