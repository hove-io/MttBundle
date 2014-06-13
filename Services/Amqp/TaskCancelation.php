<?php

/**
 * Symfony service to wrap curl calls
 * @author vdegroote
 */
namespace CanalTP\MttBundle\Services\Amqp;

use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Process\Process;

use CanalTP\MttBundle\Services\Amqp\Channel;

class TaskCancelation
{
    public $om = null;
    public $channelLib = null;
    public $taskRepo = null;

    public function __construct(ObjectManager $om, Channel $channelLib)
    {
        $this->om = $om;
        $this->channelLib = $channelLib;
        $this->taskRepo = $this->om->getRepository('CanalTPMttBundle:AmqpTask');
        $this->seasonRepo = $this->om->getRepository('CanalTPMttBundle:Season');
    }

    public function cancelAmqpMessages($season, $task)
    {
        $this->channelLib->declareQueue(
            $this->channelLib->getTaskCompletionQueueName(),
            $this->channelLib->getExchangeName(),
            "*.task_completion"
        );
        $routing_key = $this->channelLib->getRoutingKey($season, $task);
        $process = new Process('php app/console mtt:amqp:cancelTask ' . $routing_key . ' ' . $task->getId());
        $process->start();
    }

    public function cancel($taskId)
    {
        $task = $this->taskRepo->find($taskId);
        $task->cancel();
        $season = $this->seasonRepo->find($task->getObjectId());
        $season->setLocked(false);
        $this->cancelAmqpMessages($season, $task);
        $this->om->flush();
    }
}