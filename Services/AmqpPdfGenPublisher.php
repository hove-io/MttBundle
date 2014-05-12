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
use CanalTP\MttBundle\Entity\StopPoint;

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
    
    private function getAckQueueName($task, $routingKey)
    {
        // return 'ack_queue.' . $routingKey . '.task_' . $task->getId();
        return 'ack_queue.for_pdf_gen';
    }
    
    private function getRoutingKey($season)
    {
        return 'network_' . $season->getNetwork()->getId() . '.pdf_gen';;
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
        $ackQueueName = $this->getAckQueueName($task, $routingKey);
        // declare ack queue
        $this->channel->queue_declare($ackQueueName, false, true, false, false);
        $this->channel->queue_bind($ackQueueName, self::EXCHANGE_NAME, $ackQueueName);
        
        return $ackQueueName;
    }
    
    public function publish($payloads, $season)
    {
        $this->init();
        // routing_key_format: network_{networkId}.pdf_gen
        $routingKey = $this->getRoutingKey($season);
        $task = $this->getNewTask($payloads, $season);
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
    
    // TODO? Put these Task Completion Functions in a dedicated service?
    private function getLineConfig($ack, $lineConfig)
    {
        if ( 
            $lineConfig == false || 
            // check if this ack is for a different lineConfig than the previous one
            $lineConfig->getExternalLineId() != $ack->getPayload()->timetableParams->externalLineId
        ) {
            $lineConfigRepo = $this->om->getRepository('CanalTPMttBundle:LineConfig');
            $lineConfig = $lineConfigRepo->findOneBy(
                array(
                    'externalLineId' => $ack->getPayload()->timetableParams->externalLineId,
                    'season' => $ack->getPayload()->timetableParams->seasonId
                )
            );
        }
        return $lineConfig;
    }
    
    private function getTimetable($ack, $lineConfig, $timetable)
    {
        if ($timetable == false ||
            // check if this ack is for a different timetable than the previous one
            $timetable->getExternalRouteId() != $ack->getPayload()->timetableParams->externalRouteId
        ) {
            $timetableRepo = $this->om->getRepository('CanalTPMttBundle:Timetable');
            $timetable = $timetableRepo->findOneBy(
                array(
                    'externalRouteId' => $ack->getPayload()->timetableParams->externalRouteId,
                    'line_config' => $lineConfig->getId()
                )
            );
        }
        return $timetable;
    }
    
    private function getStopPoint($ack, $timetable, $stopPointRepo)
    {
        return $stopPointRepo->findOneBy(
            array(
                'timetable' => $timetable->getId(),
                'externalId' => $ack->getPayload()->timetableParams->externalStopPointId
            )
        );
    }
    
    private function completePdfGenTask($task)
    {
        echo "task n°" . $task->getId() . " completion started";
        $stopPointRepo = $this->om->getRepository('CanalTPMttBundle:StopPoint');
        $lineConfig = false;
        $timetable = false;
        foreach ($task->getAmqpAcks() as $ack) {
            if ($ack->getPayload()->generated == true) {
                $lineConfig = $this->getLineConfig($ack, $lineConfig);
                $timetable = $this->getTimetable($ack, $lineConfig, $timetable);
                $stopPoint = $this->getStopPoint($ack, $timetable, $stopPointRepo);
                if (empty($stopPoint)) {
                    $stopPoint = new StopPoint();
                    $stopPoint->setTimetable($timetable);
                    $stopPoint->setExternalId($ack->getPayload()->timetableParams->externalStopPointId);
                }
                $stopPoint->setPdfHash($ack->getPayload()->generationResult->pdfHash);
                $pdfGenerationDate = new \DateTime();
                $pdfGenerationDate->setTimestamp($ack->getPayload()->generationResult->created);
                $stopPoint->setPdfGenerationDate($pdfGenerationDate);
                $this->om->persist($stopPoint);
            }
        }
        $task->setCompleted(true);
        $task->setCompletedAt(new \DateTime("now"));
        $this->om->persist($task);
        $this->om->flush();
        echo "task n°" . $task->getId() . " completion realized";
    }
    
    public function addAckToTask($amqpMsg)
    {
        $payload = json_decode($amqpMsg->body);
        $taskRepo = $this->om->getRepository('CanalTPMttBundle:AmqpTask');
        // $seasonRepo = $this->om->getRepository('CanalTPMttBundle:Season');
        $task = $taskRepo->find($payload->taskId);
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
        echo "Completion: " . count($task->getAmqpAcks()) . " / " . $task->getJobsPublished() . "\n";
        if (count($task->getAmqpAcks()) == $task->getJobsPublished()) {
            $this->completePdfGenTask($task);
        }
    }
}