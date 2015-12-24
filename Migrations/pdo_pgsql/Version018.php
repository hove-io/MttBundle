<?php

namespace CanalTP\MttBundle\Migrations\pdo_pgsql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version018 extends AbstractMigration
{
    const VERSION = '0.1.8';

    public function up(Schema $schema)
    {
        $this->addSql('ALTER TABLE mtt.frequency ADD COLUMN columns INTEGER;');
        $this->addSql('ALTER TABLE mtt.frequency ADD COLUMN time INTEGER;');
    }

    public function down(Schema $schema)
    {
        $this->addSql('ALTER TABLE mtt.frequency DROP COLUMN columns;');
        $this->addSql('ALTER TABLE mtt.frequency DROP COLUMN time;');
    }

    public function getName()
    {
        return self::VERSION;
    }
}
