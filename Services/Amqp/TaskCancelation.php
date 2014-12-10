<?php

/**
 * Symfony service to wrap curl calls
 * @author vdegroote
 */
namespace CanalTP\MttBundle\Services\Amqp;

use Doctrine\Common\Persistence\ObjectManager;

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

    private function cancelAmqpMessages($perimeter, $task)
    {
        $routingKey = $this->channelLib->getRoutingKey($perimeter, $task);
        // get actual number of messages to set a limit
        list($queueName, $jobs, $consumers) = $this->channelLib->declareQueue(
            $this->channelLib->getPdfGenQueueName(),
            $this->channelLib->getExchangeName(),
            $routingKey
        );
        $pathToConsole = 'nohup php ' . $this->rootDir . '/console ';
        $command = $pathToConsole . 'mtt:amqp:cancelTask ' . $routingKey . ' ' . $task->getId() . ' ' . $jobs . ' > /dev/null &';
        exec($command);
    }

    public function cancel($taskId)
    {
        $task = $this->taskRepo->find($taskId);
        switch ($task->getTypeId()) {
            case AmqpTask::AREA_PDF_GENERATION_TYPE:
                break;
            case AmqpTask::SEASON_PDF_GENERATION_TYPE:
                $season = $this->seasonRepo->find($task->getObjectId());
                $season->setLocked(false);
                if ($task->isUnderProgress() == true) {
                    $this->cancelAmqpMessages($season->getPerimeter(), $task);
                }
                break;
        }
        $task->cancel();
        $this->om->flush();
    }
}
