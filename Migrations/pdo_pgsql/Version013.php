<?php

namespace CanalTP\MttBundle\Migrations\pdo_pgsql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version013 extends AbstractMigration
{
    const VERSION = '0.1.3';

    public function up(Schema $schema)
    {
        $this->addSql('ALTER TABLE mtt.timetable RENAME TO stop_timetable;');
        $this->addSql('ALTER TABLE mtt.stop_point RENAME COLUMN timetable_id TO stop_timetable_id;');
        $this->addSql('ALTER TABLE mtt.block RENAME COLUMN timetable_id TO stop_timetable_id;');
        $this->addSql('ALTER INDEX mtt.timetable_pkey RENAME TO stop_timetable_pkey');
        $this->addSql('ALTER INDEX mtt.timetable_external_route_idx RENAME TO stop_timetable_external_route_idx;');
        $this->addSql('ALTER SEQUENCE mtt.timetable_id_seq RENAME TO stop_timetable_id_seq;');
    }

    public function down(Schema $schema)
    {
        $this->addSql('ALTER TABLE mtt.stop_timetable RENAME TO timetable;');
        $this->addSql('ALTER TABLE mtt.stop_point RENAME COLUMN stop_timetable_id TO timetable_id;');
        $this->addSql('ALTER TABLE mtt.block RENAME COLUMN stop_timetable_id TO timetable_id;');
        $this->addSql('ALTER INDEX mtt.stop_timetable_pkey RENAME TO timetable_pkey');
        $this->addSql('ALTER INDEX mtt.stop_timetable_external_route_idx RENAME TO timetable_external_route_idx;');
        $this->addSql('ALTER SEQUENCE mtt.stop_timetable_id_seq RENAME TO timetable_id_seq;');
    }

    public function getName()
    {
        return self::VERSION;
    }
}
