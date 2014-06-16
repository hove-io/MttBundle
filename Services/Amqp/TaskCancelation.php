<?php

/**
 * Symfony service to wrap curl calls
 * @author vdegroote
 */
namespace CanalTP\MttBundle\Services\Amqp;

use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Process\Process;
use Spork\ProcessManager;

use CanalTP\MttBundle\Services\Amqp\Channel;

class TaskCancelation
{
    public $om = null;
    public $channelLib = null;
    public $cancelWorkerCommand = null;
    public $taskRepo = null;

    public function __construct(ObjectManager $om, Channel $channelLib, $rootDir)
    {
        $this->om = $om;
        $this->rootDir = $rootDir;
        $this->channelLib = $channelLib;
        $this->taskRepo = $this->om->getRepository('CanalTPMttBundle:AmqpTask');
        $this->seasonRepo = $this->om->getRepository('CanalTPMttBundle:Season');
    }

    public function cancelAmqpMessages($season, $task)
    {
        $routing_key = $this->channelLib->getRoutingKey($season, $task);
        $pathToConsole = 'nohup php ' . $this->rootDir . '/console ';
        $command = $pathToConsole . 'mtt:amqp:cancelTask ' . $routing_key . ' ' . $task->getId() . ' > /dev/null &';
        exec($command);
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