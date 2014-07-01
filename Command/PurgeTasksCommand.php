<?php

namespace CanalTP\MttBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Command to purge tasks.
 * Since this command takes negative values as arguments, you need to escape it in a very special manner.
 * Example:
 *  * if you want to pass -15 days:  app/console mtt:amqp:purgeTasks -- '-15 days'
 *
 * Reference:https://github.com/symfony/symfony/pull/3624
 */
class PurgeTasksCommand extends ContainerAwareCommand
{
    protected function configure()
    {
       $this
            ->setName('mtt:amqp:purgeTasks')
            ->setDescription('Purge tasks and acks. Default period to keep is one month.')
            ->addArgument(
                'older_than',
                InputArgument::OPTIONAL,
                'Period to keep. Default is "-1 month". Format must respect http://www.php.net/manual/en/datetime.formats.relative.php',
                '-1 month'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $older_than = $input->getArgument('older_than');
        $datetimeLimit = new \DateTime($older_than);
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $tasks = $em->getRepository('CanalTPMttBundle:AmqpTask')->findTasksOlderThan($datetimeLimit);
        if (count($tasks) > 0) {
            foreach ($tasks as $task) {
                $output->writeln("<info>Removing task n°" . $task->getId() . " created on " . $task->getCreated()->format('Y-m-d H:i:s') . "</info>");
                $em->remove($task);
            }
            $output->writeln("<info>Removed " . count($tasks) . " tasks</info>");
            $em->flush();
        } else {
            $output->writeln('<comment>No tasks to remove</comment>');
        }
    }
}
