<?php

/**
 * Symfony service to wrap curl calls
 * @author vdegroote
 */
namespace CanalTP\MttBundle\Services;

use Doctrine\Common\Persistence\ObjectManager;
use PhpAmqpLib\Connection\AMQPConnection;
use PhpAmqpLib\Message\AMQPMessage;

use CanalTP\MttBundle\Entity\AmqpTask;

class AmqpPdfGenPublisher
{
    const WORK_QUEUE_NAME = "pdf_gen_queue";
    const EXCHANGE_NAME = "pdf_gen_exchange";

    private $connection = null;
    private $channel = null;
    private $pdfGeneratorUrl = null;
    private $om = null;

    public function __construct(
        ObjectManager $om, 
        $pdfGeneratorUrl, 
        $amqpServerHost, 
        $user = 'guest', 
        $pass = 'guest', 
        $port = 5672, 
        $vhost = '/'
    )
    {
        $this->om = $om;
        $this->pdfGeneratorUrl = $pdfGeneratorUrl;
        $this->connection = new AMQPConnection($amqpServerHost, $port, $user, $pass, $vhost);
    }
    
    private function init()
    {
        $this->channel = $this->connection->channel();

        $this->channel->exchange_declare(self::EXCHANGE_NAME, 'topic', false, true, false);
        // pre-bind and pre-create the queue so broadcasted messages will be kept 
        // even if there is no worker listening yet
        $this->channel->queue_declare(self::WORK_QUEUE_NAME, false, true, false, false);
        // bind with routing key *.pdf_gen
        $this->channel->queue_bind(self::WORK_QUEUE_NAME, self::EXCHANGE_NAME, "*.pdf_gen");
    }
    
    private function getNewTask($payloads, $season)
    {
        $task = new AmqpTask();
        $task->setTypeId(AmqpTask::SEASON_PDF_GENERATION_TYPE);
        $task->setObjectId($season->getId());
        $task->setJobsPublished(count($payloads));
        // link to season network
        $task->setNetwork($season->getNetwork());
        $this->om->persist($task);
        $this->om->flush();
        
        return $task;
    }

    private function declareAckQueue($task, $routingKey)
    {
        $ackQueueName = 'ack_queue.' . $routingKey . '.task_' . $task->getId();
        
        // declare ack queue
        $this->channel->queue_declare($ackQueueName, false, true, false, false);
        $this->channel->queue_bind($ackQueueName, self::EXCHANGE_NAME, $ackQueueName);
        
        return $ackQueueName;
    }
    
    public function publish($payloads, $season)
    {
        $this->init();
        // routing_key_format: network_{networkId}.pdf_gen
        $routingKey = 'network_' . $season->getNetwork()->getId() . '.pdf_gen';
        $task = $this->getNewTask($payloads, $season);
        $ackQueueName = $this->declareAckQueue($task, $routingKey);
        foreach ($payloads as $payload) {
            $payload['pdfGeneratorUrl'] = $this->pdfGeneratorUrl;
            $msg = new AMQPMessage(
                json_encode($payload),
                array(
                    'delivery_mode' => 2,
                    'content_type'  => 'application/json',
                    'reply_to'      => $ackQueueName
                )
            );
            $this->channel->basic_publish($msg, self::EXCHANGE_NAME, $routingKey, true);
        }
    }
}
