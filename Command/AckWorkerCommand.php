<?php

namespace CanalTP\MttBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;

use PhpAmqpLib\Message\AMQPMessage;

class AckWorkerCommand extends ContainerAwareCommand
{
    private $channel = null;
    private $channelLib = null;

    private function initChannel()
    {
        $this->channelLib = $this->getContainer()->get('canal_tp_mtt.amqp_channel');
        $this->channel = $this->channelLib->getChannel();
    }

    public function process_message($msg)
    {
        try {
            $task = $this->amqpPdfGenPublisher->addAckToTask($msg);
            echo " [x] Ack Inserted \n";
            echo "Task n°" . $task->getId() . " : " . count($task->getAmqpAcks()) . " / " . $task->getJobsPublished() . "\n";
            if (count($task->getAmqpAcks()) == $task->getJobsPublished()) {
                $msgCompleted = new AMQPMessage(
                    'Completed',
                    array('delivery_mode' => 2) # make message persistent
                );
                $this->channel->basic_publish(
                    $msgCompleted, 
                    $this->channelLib->getExchangeFanoutName(), 
                    $task->getId().'.task_completion', 
                    true
                );
                $pdfGenCompletionLib = $this->getContainer()->get('canal_tp_mtt.pdf_gen_completion_lib');
                echo "StartCompleted\n";
                $pdfGenCompletionLib->completePdfGenTask($task);
            }
            echo "\n--------\n";
        } catch (\Exception $e) {
            echo "ERROR during acking process" . print_r($msg->body) . "\n";
            echo $e->getMessage() . "\n";
        }
        $msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);
        // acknowledge broker
    }

    private function runProcess($ack_queue_name)
    {
        $this->amqpPdfGenPublisher = $this->getContainer()->get('canal_tp_mtt.amqp_pdf_gen_publisher');

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
            ->addArgument(
                'ack_queue_name',
                InputArgument::OPTIONAL,
                'Acknowledgement queue name',
                'ack_queue.for_pdf_gen'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->initChannel();
        $ack_queue_name = $input->getArgument('ack_queue_name');
        // init queue just in case
        $this->channelLib->declareQueue($ack_queue_name, $this->channelLib->getExchangeName(), $ack_queue_name);
        $this->runProcess($input->getArgument('ack_queue_name'));
        $this->channelLib->close();
    }
}
