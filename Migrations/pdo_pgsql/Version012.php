<?php

namespace CanalTP\MttBundle\Migrations\pdo_pgsql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version012 extends AbstractMigration
{
    const VERSION = '0.1.2';

    public function up(Schema $schema)
    {
        // Adding LineTimetable
        $this->addSql('
            CREATE TABLE mtt.line_timetable (
                id SERIAL NOT NULL,
                line_config_id INT DEFAULT NULL REFERENCES mtt.line_config(id) NOT DEFERRABLE INITIALLY IMMEDIATE,
                created TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
                updated TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
                PRIMARY KEY(id)
            );
        ');
        $this->addSql('CREATE INDEX IDX_6D2F8CFA94D8FDF1 ON mtt.line_timetable (line_config_id);');

        // Adding Template
        $this->addSql('
            CREATE TABLE mtt.template (
                id SERIAL NOT NULL,
                type VARCHAR(255) NOT NULL,
                path VARCHAR(255) NOT NULL,
                created TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
                updated TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
                PRIMARY KEY(id)
            );
        ');

        // Adding LayoutTemplate relation table
        $this->addSql('
            CREATE TABLE mtt.layout_template (
                layout_id INT NOT NULL REFERENCES mtt.layout (id) NOT DEFERRABLE INITIALLY IMMEDIATE,
                template_id INT NOT NULL REFERENCES mtt.template (id) NOT DEFERRABLE INITIALLY IMMEDIATE,
                PRIMARY KEY(layout_id, template_id)
            );
        ');
        $this->addSql('CREATE INDEX IDX_23C4B5A38C22AA1A ON mtt.layout_template (layout_id);');
        $this->addSql('CREATE INDEX IDX_23C4B5A35DA0FB8 ON mtt.layout_template (template_id);');

        // Moving path from Layout to Template
        $this->addSql('
            ALTER TABLE mtt.layout
            DROP COLUMN path;
        ');
    }

    public function down(Schema $schema)
    {
        // Moving path from Template to Layout
        $this->addSql('
            ALTER TABLE mtt.layout
            ADD COLUMN path
            VARCHAR(255);
        ');

        // Removing LayoutTemplate
        $this->addSql('DROP TABLE mtt.layout_template;');

        // Removing Template
        $this->addSql('DROP TABLE mtt.template;');

        // Removing LineTimetable
        $this->addSql('DROP TABLE mtt.line_timetable;');
    }

    public function getName()
    {
        return self::VERSION;
    }
}
