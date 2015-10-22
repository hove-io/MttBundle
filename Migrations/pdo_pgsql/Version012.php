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
                id INT NOT NULL,
                line_config_id INT DEFAULT NULL,
                created TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
                updated TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
                PRIMARY KEY(id)
            );
        ');
        $this->addSql('CREATE SEQUENCE mtt.line_timetable_id_seq INCREMENT BY 1 MINVALUE 1 START 1;');
        $this->addSql('CREATE INDEX IDX_6D2F8CFA94D8FDF1 ON mtt.line_timetable (line_config_id);');
        $this->addSql('
            ALTER TABLE mtt.line_timetable
            ADD CONSTRAINT FK_6D2F8CFA94D8FDF1
            FOREIGN KEY (line_config_id)
            REFERENCES mtt.line_config (id)
            NOT DEFERRABLE INITIALLY IMMEDIATE;
        ');

        // Adding Template
        $this->addSql('
            CREATE TABLE mtt.template (
                id INT NOT NULL,
                type VARCHAR(255) NOT NULL,
                path VARCHAR(255) NOT NULL,
                created TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
                updated TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
                PRIMARY KEY(id)
            );
        ');
        $this->addSql('CREATE SEQUENCE mtt.template_id_seq INCREMENT BY 1 MINVALUE 1 START 1;');

        // Adding LayoutTemplate relation table
        $this->addSql('
            CREATE TABLE mtt.layout_template (
                layout_id INT NOT NULL,
                template_id INT NOT NULL,
                PRIMARY KEY(layout_id, template_id)
            );
        ');
        $this->addSql('CREATE INDEX IDX_23C4B5A38C22AA1A ON mtt.layout_template (layout_id);');
        $this->addSql('CREATE INDEX IDX_23C4B5A35DA0FB8 ON mtt.layout_template (template_id);');
        $this->addSql('
            ALTER TABLE mtt.layout_template
            ADD CONSTRAINT FK_23C4B5A38C22AA1A
            FOREIGN KEY (layout_id)
            REFERENCES mtt.layout (id)
            NOT DEFERRABLE INITIALLY IMMEDIATE;
        ');
        $this->addSql('
            ALTER TABLE mtt.layout_template
            ADD CONSTRAINT FK_23C4B5A35DA0FB8
            FOREIGN KEY (template_id)
            REFERENCES mtt.template (id)
            NOT DEFERRABLE INITIALLY IMMEDIATE;
        ');

        // Moving path from Layout to Template
        $this->addSql('
            ALTER TABLE mtt.layout
            DROP COLUMN path;
        ');
    }

    public function down(Schema $schema)
    {
        // Removing LineTimetable
        $this->addSql('DROP TABLE mtt.line_timetable;');
        $this->addSql('DROP SEQUENCE mtt.line_timetable_id_seq;');

        // Removing LayoutTemplate
        $this->addSql('DROP TABLE mtt.layout_template;');

        // Removing Template
        $this->addSql('DROP TABLE mtt.template;');
        $this->addSql('DROP SEQUENCE mtt.template_id_seq;');

        // Moving path from Template to Layout
        $this->addSql('
            ALTER TABLE mtt.layout
            ADD COLUMN path
            VARCHAR(255);
        ');
    }

    public function getName()
    {
        return self::VERSION;
    }
}
