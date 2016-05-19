<?php

namespace CanalTP\MttBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;

use PhpAmqpLib\Message\AMQPMessage;

class CancelWorkerCommand extends ContainerAwareCommand
{
    private $logger = null;
    private $channel = null;
    private $channelLib = null;
    private $routingKeyToCancel = null;
    private $taskCompleted = false;
    private $msgLimit = 0;
    private $msgExamined = 0;

    private function initChannel()
    {
        $this->channelLib = $this->getContainer()->get('canal_tp_mtt.amqp_channel');
        $this->channel = $this->channelLib->getChannel();
        $this->channel->basic_qos(null, 1, null);
        $this->i = 0;
    }

    public function watchTaskCompletion($msg)
    {
        $this->logger->info("Task completion queue " . $msg->delivery_info['routing_key']);
        if ($msg->delivery_info['routing_key'] == $this->taskId . ".task_completion") {
            $this->logger->info(" [x] Task completion confirmed for " . $msg->delivery_info['routing_key']);
            $this->taskCompleted = true;
        }
    }

    public function processMessage($msg)
    {
        $msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);

        if ($msg->delivery_info['routing_key'] == $this->routingKeyToCancel) {
            $this->logger->info(" [x] Cancelled" . $msg->delivery_info['routing_key']);
            $payload = json_decode($msg->body);
            $payload->generated = false;
            $payload->cancelled = true;
            $ackMsg = new AMQPMessage(
                json_encode($payload),
                array(
                    'delivery_mode' => 2,
                    'content_type'  => 'application/json'
                )
            );
            // publish to ack queue
            $msg->delivery_info['channel']->basic_publish(
                $ackMsg,
                $this->channelLib->getExchangeName(),
                $msg->get('reply_to'),
                true
            );
        } else {
            $this->logger->info(" [x] Republished " . $msg->delivery_info['routing_key']);
            $newMsg = new AMQPMessage(
                $msg->body,
                array(
                    'delivery_mode' => 2,
                    'content_type'  => 'application/json',
                    'reply_to'      => $msg->get('reply_to')
                )
            );
            $this->channel->basic_publish($newMsg, $this->channelLib->getExchangeName(), $msg->delivery_info['routing_key'], true);
        }
        $this->msgExamined++;
    }

    private function runProcess($routingKey, $taskId, $msgLimit)
    {
        $this->taskId = $taskId;
        $this->routingKeyToCancel = $routingKey;
        $this->msgLimit = $msgLimit;

        $this->channel->basic_consume(
            $this->channelLib->getPdfGenQueueName(),
            'cancelTask',
            false,
            false,
            false,
            false,
            array($this, 'processMessage'),
            null,
            array('x-priority' => array('I', 100))
        );
        list($queueName, $jobs, $consumers) = $this->channel->queue_declare('', false, false, true, true);
        $this->channel->queue_bind($queueName, $this->channelLib->getExchangeFanoutName());
        $this->channel->basic_consume(
            $queueName,
            'taskCompletion',
            false,
            true,
            false,
            false,
            array($this, 'watchTaskCompletion')
        );
        while ($this->taskCompleted == false && ($this->msgLimit != 0 && $this->msgExamined < $this->msgLimit || $this->msgLimit == 0)) {
            $this->logger->info("Task Completed: " . $this->taskCompleted);
            $this->logger->info("msg Examined: " . $this->msgExamined);
            $this->logger->info("msg limit: " . $this->msgLimit);
            $this->channel->wait();
        }
    }

    protected function configure()
    {
        $this
            ->setName('mtt:amqp:cancelTask')
            ->setDescription('Launch a amqp listener to get acknowledgements from pdf generation workers and log these into database')
            ->addArgument('routing_key', InputArgument::REQUIRED, 'Routing Key messages to cancel')
            ->addArgument('task_id', InputArgument::REQUIRED, 'Task Id to cancel')
            ->addArgument('limit', InputArgument::OPTIONAL, 'Limit of messages to examine. Default 0.', 0);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->initChannel();
        $this->logger = $this->getContainer()->get('logger');
        $this->runProcess(
            $input->getArgument('routing_key'),
            $input->getArgument('task_id'),
            $input->getArgument('limit')
        );
        $this->channelLib->close();
        exit();
    }
}
