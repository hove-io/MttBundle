<?php

/**
 * Symfony service to wrap curl calls
 * @author vdegroote
 */
namespace CanalTP\MttBundle\Services\Amqp;

use PhpAmqpLib\Connection\AMQPConnection;

class Channel
{
    const EXCHANGE_NAME = "pdf_gen_exchange";
    const EXCHANGE_FANOUT_NAME = "fanout_exchange";
    const PDF_GEN_QUEUE_NAME = "pdf_gen_queue";

    private $connection = null;
    private $channel = null;

    public function __construct(
        $amqpServerHost,
        $user,
        $pass,
        $port,
        $vhost
    ) {
    
        $this->connection = new AMQPConnection($amqpServerHost, $port, $user, $pass, $vhost);

    }

    private function init()
    {
        if (empty($this->channel)) {
            $this->channel = $this->connection->channel();
            $this->channel->exchange_declare($this->getExchangeName(), 'topic', false, true, false);
            $this->channel->exchange_declare($this->getExchangeFanoutName(), 'fanout', false, true, false);
        }
    }

    public function getExchangeFanoutName()
    {
        return self::EXCHANGE_FANOUT_NAME;
    }

    public function declareQueue($queueName, $exchangeName, $routingKey)
    {
        $this->init();
        $return = $this->channel->queue_declare($queueName, false, true, false, false);
        // bind with routing key
        $this->channel->queue_bind($queueName, $exchangeName, $routingKey);

        return $return;
    }

    public function getChannel()
    {
        $this->init();

        return $this->channel;
    }

    public function declareAckQueue()
    {
        // declare ack queue
        $ackQueueName = $this->getAckQueueName();
        $this->declareQueue($ackQueueName, $this->getExchangeName(), $ackQueueName);

        return $ackQueueName;
    }

    public function getAckQueueName()
    {
        return 'ack_queue.for_pdf_gen';
    }

    public function getRoutingKey($perimeter, $task)
    {
        return 'network_' . $perimeter->getId() . '_task_' . $perimeter->getId() .'.pdf_gen';
    }

    public function getExchangeName()
    {
        return self::EXCHANGE_NAME;
    }

    public function getPdfGenQueueName()
    {
        return self::PDF_GEN_QUEUE_NAME;
    }

    public function close()
    {
        $this->channel->close();
        $this->connection->close();
    }
}
