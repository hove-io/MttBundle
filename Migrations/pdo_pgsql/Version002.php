<?php

namespace CanalTP\MttBundle\Migrations\pdo_pgsql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version002 extends AbstractMigration
{
    //MTT VERSION 0.7.1
    const VERSION = '0.0.2';

    public function getName()
    {
        return self::VERSION;
    }

    public function up(Schema $schema)
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "postgresql", "Migration can only be executed safely on 'postgresql'.");

        // add pdf_hash column
        $this->addSql('ALTER TABLE mtt.stop_point ADD pdf_hash VARCHAR(32) DEFAULT NULL;');
        // add css_version column
        $this->addSql('ALTER TABLE mtt.layout ADD css_version INT DEFAULT 0;');
        // add publication column to seasons
        $this->addSql('ALTER TABLE mtt.season ADD published BOOLEAN DEFAULT FALSE;');
    }

    public function down(Schema $schema)
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "postgresql", "Migration can only be executed safely on 'postgresql'.");

        // add pdf_hash column
        $this->addSql('ALTER TABLE mtt.stop_point DROP IF EXISTS pdf_hash;');
        // add css_version column
        $this->addSql('ALTER TABLE mtt.stop_point DROP IF EXISTS css_version;');
        $this->addSql('ALTER TABLE mtt.season DROP IF EXISTS published;');
    }
}
