<?php

namespace CanalTP\MttBundle\Migrations\pdo_pgsql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use CanalTP\MttBundle\Entity\AmqpTask;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version011 extends AbstractMigration
{
    const VERSION = '0.1.1';

    public function postUp(Schema $schema)
    {
        $statement = $this->connection->prepare('DELETE FROM mtt.amqp_ack WHERE amqp_task_id IN (SELECT id FROM mtt.amqp_task WHERE type_id = ' . AmqpTask::DISTRIBUTION_LIST_PDF_GENERATION_TYPE . ')');
        $statement->execute();
        $statement = $this->connection->prepare('DELETE FROM mtt.amqp_task WHERE type_id = ' . AmqpTask::DISTRIBUTION_LIST_PDF_GENERATION_TYPE);
        $statement->execute();
    }

    public function up(Schema $schema)
    {
        $this->addSql('ALTER TABLE mtt.distribution_list RENAME TO __distribution_list;');
    }

    public function down(Schema $schema)
    {
        $this->addSql('ALTER TABLE mtt.__distribution_list RENAME TO distribution_list;');
    }

    public function getName()
    {
        return self::VERSION;
    }
}
