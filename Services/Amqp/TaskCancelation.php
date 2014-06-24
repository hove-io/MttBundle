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
use CanalTP\MttBundle\Entity\AmqpTask;

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

    public function cancelAmqpMessages($network, $task)
    {
        $routingKey = $this->channelLib->getRoutingKey($network, $task);
        $pathToConsole = 'nohup php ' . $this->rootDir . '/console ';
        $command = $pathToConsole . 'mtt:amqp:cancelTask ' . $routingKey . ' ' . $task->getId() . ' &';
        exec($command);
    }

    public function cancel($taskId)
    {
        $task = $this->taskRepo->find($taskId);
        $task->cancel();
        switch($task->getTypeId()){
            case AmqpTask::DISTRIBUTION_LIST_PDF_GENERATION_TYPE:
                break;
            case AmqpTask::SEASON_PDF_GENERATION_TYPE:
                $season = $this->seasonRepo->find($task->getObjectId());
                $season->setLocked(false);
                $this->cancelAmqpMessages($season->getNetwork(), $task);
                break;
        }
        $this->om->flush();
    }
}