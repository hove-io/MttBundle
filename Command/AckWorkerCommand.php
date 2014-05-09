<?php

namespace CanalTP\MttBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use PhpAmqpLib\Connection\AMQPConnection;
use PhpAmqpLib\Message\AMQPMessage;
use CanalTP\MttBundle\Services\AmqpPdfGenPublisher;

class AckWorkerCommand extends ContainerAwareCommand
{
    private $channel = null;
    private $connection = null;
    private $amqpGenPublisher = null;

    private function initChannel()
    {
        $amqpServerHost = $this->getContainer()->getParameter('amqp_server_host');
        $port = $this->getContainer()->getParameter('amqp_server_port');
        $user = $this->getContainer()->getParameter('amqp_server_user');
        $pass = $this->getContainer()->getParameter('amqp_server_pass');
        $vhost = $this->getContainer()->getParameter('amqp_server_vhost');

        $this->connection = new AMQPConnection($amqpServerHost, $port, $user, $pass, $vhost);
        $this->channel = $this->connection->channel();
        $this->channel->basic_qos(null, 1, null);
        $this->channel->exchange_declare(AmqpPdfGenPublisher::EXCHANGE_NAME, 'topic', false, true, false);
    }
    
    public function process_message($msg)
    {
        $this->amqpGenPublisher->addAckToTask($msg);
        echo " [x] Ack Inserted \n";
        // acknowledge broker
        $msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);
    }

    private function runProcess($ack_queue_name)
    {
        $this->amqpGenPublisher = $this->getContainer()->get('canal_tp_mtt.amqp_pdf_gen_publisher');

        $this->channel->basic_consume(
            $ack_queue_name, 
            'pdfAckWorker', 
            false, 
            false, 
            false, 
            false, 
            array($this, 'process_message')
        );
        while (count($this->channel->callbacks)) {
            $this->channel->wait();
        }
    }

    protected function configure()
    {
       $this
            ->setName('mtt:amqp:waitForAcks')
            ->setDescription('Launch a amqp listener to get acknowledgements from pdf generation workers and log these into database')
            ->addArgument('ack_queue_name', InputArgument::REQUIRED, 'Acknowledgement queue name');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->initChannel($input);
        $this->runProcess($input->getArgument('ack_queue_name'));
        $this->channel->close();
        $this->connection->close();
    }
}
