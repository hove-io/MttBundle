<?php

namespace CanalTP\MttBundle\Migrations\pdo_pgsql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version016 extends AbstractMigration
{
    const VERSION = '0.1.6';

    public function up(Schema $schema)
    {
        $this->addSql('ALTER TABLE mtt.block ADD COLUMN rank INTEGER default 0;');
        $this->addSql('ALTER TABLE mtt.block ADD COLUMN external_line_id VARCHAR(255);');
        $this->addSql('ALTER TABLE mtt.block ADD COLUMN external_route_id VARCHAR(255);');
        $this->addSql('ALTER TABLE mtt.block ADD COLUMN line_timetable_id INT REFERENCES mtt.line_timetable(id) NOT DEFERRABLE INITIALLY IMMEDIATE;');
    }

    public function down(Schema $schema)
    {
        $this->addSql('ALTER TABLE mtt.block DROP COLUMN rank;');
        $this->addSql('ALTER TABLE mtt.block DROP COLUMN external_line_id;');
        $this->addSql('ALTER TABLE mtt.block DROP COLUMN external_route_id;');
        $this->addSql('ALTER TABLE mtt.block DROP COLUMN line_timetable_id;');
    }

    public function getName()
    {
        return self::VERSION;
    }
}
