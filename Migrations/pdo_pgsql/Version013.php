<?php

namespace CanalTP\MttBundle\Migrations\pdo_pgsql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use CanalTP\MttBundle\Entity\AmqpTask;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version013 extends AbstractMigration
{
    const VERSION = '0.1.3';

    public function postUp(Schema $schema)
    {
    }

    public function up(Schema $schema)
    {
        $this->addSql('ALTER TABLE mtt.frequency ADD COLUMN filled_column bigint;');
    }

    public function down(Schema $schema)
    {
    }

    public function getName()
    {
        return self::VERSION;
    }
}
