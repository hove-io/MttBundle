<?php

namespace CanalTP\MttBundle\Migrations\pdo_pgsql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version014 extends AbstractMigration
{
    const VERSION = '0.1.4';

    public function up(Schema $schema)
    {
        $this->addSql('CREATE TABLE mtt.selected_stop_point (
            id SERIAL NOT NULL,
            line_timetable_id INT NOT NULL REFERENCES mtt.line_timetable (id) NOT DEFERRABLE INITIALLY IMMEDIATE,
            external_route_id VARCHAR(255) NOT NULL,
            external_stop_point_id VARCHAR(255) NOT NULL,
            rank INT NOT NULL,
            created TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
            updated TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
            PRIMARY KEY(id)
        );');
        $this->addSql('CREATE INDEX IDX_B761A145F504BD05 ON mtt.selected_stop_point (line_timetable_id);');
    }

    public function down(Schema $schema)
    {
        $this->addSql('DROP TABLE mtt.selected_stop_point;');
    }

    public function getName()
    {
        return self::VERSION;
    }
}
