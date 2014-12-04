<?php

namespace CanalTP\MttBundle\Migrations\pdo_pgsql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version006 extends AbstractMigration
{
    const VERSION = '0.0.6';

    public function up(Schema $schema)
    {
        $this->addSql('ALTER TABLE mtt.layout ADD notes_mode INT DEFAULT 0;');
    }

    public function down(Schema $schema)
    {
        $this->addSql('ALTER TABLE mtt.layout DROP IF EXISTS notes_mode;');
    }

    public function getName()
    {
        return self::VERSION;
    }
}
