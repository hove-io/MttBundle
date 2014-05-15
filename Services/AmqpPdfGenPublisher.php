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
use CanalTP\MttBundle\Entity\AmqpAck;

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
        $user, 
        $pass, 
        $port, 
        $vhost
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
    
    private function getAckQueueName($task, $routingKey)
    {
        // return 'ack_queue.' . $routingKey . '.task_' . $task->getId();
        return 'ack_queue.for_pdf_gen';
    }
    
    private function getRoutingKey($season, $task)
    {
        return 'network_' . $season->getNetwork()->getId() . '_task_' . $task->getId() .'.pdf_gen';;
    }

    private function getNewTask($payloads, $season, $taskOptions)
    {
        $task = new AmqpTask();
        $task->setTypeId(AmqpTask::SEASON_PDF_GENERATION_TYPE);
        $task->setObjectId($season->getId());
        $task->setJobsPublished(count($payloads));
        $task->setOptions($taskOptions);
        // link to season network
        $task->setNetwork($season->getNetwork());
        $this->om->persist($task);
        $this->om->flush();
        
        return $task;
    }

    private function declareAckQueue($task, $routingKey)
    {
        $ackQueueName = $this->getAckQueueName($task, $routingKey);
        // declare ack queue
        $this->channel->queue_declare($ackQueueName, false, true, false, false);
        $this->channel->queue_bind($ackQueueName, self::EXCHANGE_NAME, $ackQueueName);
        
        return $ackQueueName;
    }
    
    public function publish($payloads, $season, $taskOptions = array())
    {
        $this->init();
        // routing_key_format: network_{networkId}.pdf_gen
        $task = $this->getNewTask($payloads, $season, $taskOptions);
        $routingKey = $this->getRoutingKey($season, $task);
        $ackQueueName = $this->declareAckQueue($task, $routingKey);
        foreach ($payloads as $payload) {
            $payload['pdfGeneratorUrl'] = $this->pdfGeneratorUrl;
            $payload['taskId'] = $task->getId();
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
        
    public function addAckToTask($amqpMsg)
    {
        $payload = json_decode($amqpMsg->body);
        $taskRepo = $this->om->getRepository('CanalTPMttBundle:AmqpTask');
        // $seasonRepo = $this->om->getRepository('CanalTPMttBundle:Season');
        $task = $taskRepo->find($payload->taskId);
        if (!empty($task)) {
            $ack = new AmqpAck();
            $ack->setPayload($payload);
            $ack->setAmqpTask($task);
            $deliveryInfo = array();
            $deliveryInfo['consumer_tag'] = $amqpMsg->delivery_info['consumer_tag'];
            $deliveryInfo['delivery_tag'] = $amqpMsg->delivery_info['delivery_tag'];
            $deliveryInfo['redelivered'] = $amqpMsg->delivery_info['redelivered'];
            $deliveryInfo['exchange'] = $amqpMsg->delivery_info['exchange'];
            $deliveryInfo['routing_key'] = $amqpMsg->delivery_info['routing_key'];
            $ack->setDeliveryInfo($deliveryInfo);
            $this->om->persist($ack);
            $this->om->flush();
            $this->om->refresh($task);
        } else {
            throw new \Exception('An ack has been sent for a non-existent task');
        }
        return $task;
    }
}