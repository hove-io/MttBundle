<?php

namespace CanalTP\MttBundle\Migrations\pdo_pgsql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version004 extends AbstractMigration
{
    const VERSION = '0.0.4';

    public function getName()
    {
        return self::VERSION;
    }

    public function up(Schema $schema)
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "postgresql", "Migration can only be executed safely on 'postgresql'.");

        //create table mtt.amqp_task
        $this->addSql('CREATE TABLE mtt.amqp_task (id SERIAL NOT NULL, network_id INT DEFAULT NULL, type_id INT NOT NULL, object_id INT NOT NULL, status INT NOT NULL, jobs_published INT NOT NULL, options TEXT NOT NULL, completedAt TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, created TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id));');
        //create Index
        $this->addSql('CREATE INDEX idx_f63afecb34128b91 ON mtt.amqp_task (network_id);');
        $this->addSql('COMMENT ON COLUMN mtt.amqp_task.options IS \'(DC2Type:array)\';');
        //add foreign key
        $this->addSql('ALTER TABLE mtt.amqp_task ADD CONSTRAINT fk_f63afecb34128b91 FOREIGN KEY (network_id) REFERENCES mtt.network (id) NOT DEFERRABLE INITIALLY IMMEDIATE;');
        //create table mtt.amqp_ack
        $this->addSql('CREATE TABLE mtt.amqp_ack (id SERIAL NOT NULL, amqp_task_id INT DEFAULT NULL, payload TEXT NOT NULL, deliveryInfo TEXT NOT NULL, created TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id));');
        $this->addSql('CREATE INDEX IDX_B7057BD726962FD6 ON mtt.amqp_ack (amqp_task_id);');
        $this->addSql('COMMENT ON COLUMN mtt.amqp_ack.payload IS \'(DC2Type:object)\';');
        $this->addSql('COMMENT ON COLUMN mtt.amqp_ack.deliveryInfo IS \'(DC2Type:array)\';');
        $this->addSql('ALTER TABLE mtt.amqp_ack ADD CONSTRAINT FK_B7057BD726962FD6 FOREIGN KEY (amqp_task_id) REFERENCES mtt.amqp_task (id) NOT DEFERRABLE INITIALLY IMMEDIATE;');
    }

    public function down(Schema $schema)
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "postgresql", "Migration can only be executed safely on 'postgresql'.");

        $this->addSql('DROP INDEX mtt.IDX_B7057BD726962FD6;');
        $this->addSql('DROP TABLE mtt.amqp_ack;');

        $this->addSql('DROP INDEX mtt.idx_f63afecb34128b91;');
        $this->addSql('DROP TABLE mtt.amqp_task;');
    }
}
