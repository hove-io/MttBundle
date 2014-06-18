<?php

namespace CanalTP\MttBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use PhpAmqpLib\Message\AMQPMessage;

class CancelWorkerCommand extends ContainerAwareCommand
{
    private $channel = null;
    private $channelLib = null;
    private $routingKeyToCancel = null;
    private $taskCompleted = false;

    private function initChannel()
    {
        $this->channelLib = $this->getContainer()->get('canal_tp_mtt.amqp_channel');
        $this->channel = $this->channelLib->getChannel();
        $this->channel->basic_qos(null, 1, null);
    }
    
    public function watchTaskCompletion($msg)
    {
        echo "Task completion queue ", $msg->delivery_info['routing_key'];
        if ($msg->delivery_info['routing_key'] == $this->taskId . ".task_completion") {
            echo " [x] Task completion confirmed for ", $msg->delivery_info['routing_key'], "\n";
            $this->taskCompleted = true;
        }
    }

    public function process_message($msg)
    {
        // echo $msg->delivery_info['routing_key'], " ---- ", $this->routingKeyToCancel, "\r\n";
        if ($msg->delivery_info['routing_key'] == $this->routingKeyToCancel) {
            $msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);
            echo " [x] Cancelled", $msg->delivery_info['routing_key'], "\n";
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
            // echo " [x] Not Cancelled", $msg->delivery_info['routing_key'], "\n";
            $msg->delivery_info['channel']->basic_nack($msg->delivery_info['delivery_tag'], false, true);
        }
    }

    private function runProcess($routingKey, $taskId)
    {
        $this->taskId = $taskId;
        $this->routingKeyToCancel = $routingKey;

        // echo "bind to ", $this->routingKeyToCancel, "\r\n";die;
        $this->channel->queue_bind($this->channelLib->getPdfGenQueueName(), $this->channelLib->getExchangeName(), $this->routingKeyToCancel);
        
        $this->channel->basic_consume(
            $this->channelLib->getPdfGenQueueName(),
            'cancelTask', 
            false, 
            false, 
            false, 
            false, 
            array($this, 'process_message'), 
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
        while ($this->taskCompleted == false) {
            $this->channel->wait();
        }
    }

    protected function configure()
    {
       $this
            ->setName('mtt:amqp:cancelTask')
            ->setDescription('Launch a amqp listener to get acknowledgements from pdf generation workers and log these into database')
            ->addArgument('routing_key', InputArgument::REQUIRED, 'Routing Key messages to cancel')
            ->addArgument('task_id', InputArgument::REQUIRED, 'Task Id to cancel');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->initChannel();
        $this->runProcess($input->getArgument('routing_key'), $input->getArgument('task_id'));
        $this->channelLib->close();
    }
}
