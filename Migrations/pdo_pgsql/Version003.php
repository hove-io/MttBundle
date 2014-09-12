<?php

namespace CanalTP\MttBundle\Migrations\pdo_pgsql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version003 extends AbstractMigration
{
    //MTT VERSION 0.8.1
    const VERSION = '0.0.3';

    public function getName()
    {
        return self::VERSION;
    }

    public function up(Schema $schema)
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "postgresql", "Migration can only be executed safely on 'postgresql'.");

        // add token column in network table
        $this->addSql('ALTER TABLE mtt.network ADD token VARCHAR(255) DEFAULT NULL;');
    }

    public function down(Schema $schema)
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "postgresql", "Migration can only be executed safely on 'postgresql'.");

        // remove token column in network table
        $this->addSql('ALTER TABLE mtt.network DROP IF EXISTS token;');
    }
}
