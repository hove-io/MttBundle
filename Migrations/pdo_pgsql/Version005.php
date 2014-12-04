<?php

namespace CanalTP\MttBundle\Migrations\pdo_pgsql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version005 extends AbstractMigration
{
    const VERSION = '0.0.5';

    public function getName()
    {
        return self::VERSION;
    }

    public function up(Schema $schema)
    {
        // add publication column to seasons
        $this->addSql('ALTER TABLE mtt.season ADD locked BOOLEAN DEFAULT FALSE;');
    }

    public function down(Schema $schema)
    {
        // add pdf_hash column
        $this->addSql('ALTER TABLE mtt.stop_point DROP IF EXISTS locked;');
    }
}
