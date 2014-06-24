<?php

namespace CanalTP\MttBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class PurgeTasksCommand extends ContainerAwareCommand
{
    protected function configure()
    {
       $this
            ->setName('mtt:amqp:purgeTasks')
            ->setDescription('Launch a amqp listener to get acknowledgements from pdf generation workers and log these into database')
            ->addArgument(
                'ack_queue_name', 
                InputArgument::OPTIONAL, 
                'Acknowledgement queue name', 
                'ack_queue.for_pdf_gen'
            );
    }
}