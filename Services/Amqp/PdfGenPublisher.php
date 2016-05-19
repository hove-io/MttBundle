<?php

/**
 * Symfony service to wrap curl calls
 * @author vdegroote
 */
namespace CanalTP\MttBundle\Services\Amqp;

use Doctrine\Common\Persistence\ObjectManager;
use PhpAmqpLib\Message\AMQPMessage;

use CanalTP\MttBundle\Entity\AmqpTask;
use CanalTP\MttBundle\Entity\AmqpAck;

class PdfGenPublisher
{
    private $channel = null;
    private $channelLib = null;
    private $exchangeName = null;
    private $pdfGeneratorUrl = null;
    private $om = null;

    public function __construct(
        ObjectManager $om,
        $pdfGeneratorUrl,
        Channel $amqpChannelLib
    ) {
    
        $this->om = $om;
        $this->channelLib = $amqpChannelLib;
        $this->pdfGeneratorUrl = $pdfGeneratorUrl;
        $this->channel = $amqpChannelLib->getChannel();
        $this->exchangeName = $amqpChannelLib->getExchangeName();
        $this->queueName = $amqpChannelLib->getPdfGenQueueName();
    }

    private function init()
    {
        // pre-bind and pre-create the queue so broadcasted messages will be kept
        // even if there is no worker listening yet
        $this->channelLib->declareQueue($this->queueName, $this->exchangeName, "*.pdf_gen");
    }

    private function getNewTask($payloads, $object, $perimeter, $taskOptions, $taskType = AmqpTask::SEASON_PDF_GENERATION_TYPE)
    {
        $task = new AmqpTask();
        $task->setTypeId($taskType);
        $task->setObjectId($object->getId());
        $task->setJobsPublished(count($payloads));
        $task->setOptions($taskOptions);
        $task->setPerimeter($perimeter);
        $this->om->persist($task);
        $this->om->flush();

        return $task;
    }

    private function lockSeason($season)
    {
        $season->setLocked(true);
        $this->om->flush();
    }

    private function publishPayloads($payloads, $task)
    {
        $this->init();
        $routingKey = $this->channelLib->getRoutingKey($task->getPerimeter(), $task);
        $ackQueueName = $this->channelLib->declareAckQueue();
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
            $this->channel->basic_publish($msg, $this->exchangeName, $routingKey, true);
        }
    }

    public function publishAreaPdfGen($payloads, $areaPdf, $taskOptions = array())
    {
        $task = $this->getNewTask($payloads, $areaPdf, $areaPdf->getArea()->getPerimeter(), $taskOptions, AmqpTask::AREA_PDF_GENERATION_TYPE);
        $this->publishPayloads($payloads, $task);
    }

    public function publishSeasonPdfGen($payloads, $season, $taskOptions = array())
    {
        // routing_key_format: network_{networkId}.pdf_gen
        $task = $this->getNewTask($payloads, $season, $season->getPerimeter(), $taskOptions);
        $this->publishPayloads($payloads, $task);
        $this->lockSeason($season);
    }

    public function addAckToTask($amqpMsg)
    {
        $payload = json_decode($amqpMsg->body);
        $taskRepo = $this->om->getRepository('CanalTPMttBundle:AmqpTask');

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
