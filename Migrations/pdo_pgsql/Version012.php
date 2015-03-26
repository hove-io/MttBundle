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

    public function postUp(Schema $schema)
    {
        $this->addSql('ALTER TABLE mtt.layout ADD COLUMN "configuration" text;');
    }

    public function up(Schema $schema)
    {
        $this->addSql("CREATE TABLE mtt.line_timecard (
            id SERIAL NOT NULL,
            perimeter_id INT DEFAULT NULL,
            line_config_id INT DEFAULT NULL,
            line_id VARCHAR(255) NOT NULL,
            created TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
            updated TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
            PRIMARY KEY(id)
        );");

        $this->addSql("CREATE TABLE mtt.timecard (
            id SERIAL NOT NULL,
            perimeter_id INT DEFAULT NULL,
            line_timecard_id INT DEFAULT NULL,
            lineId VARCHAR(255) NOT NULL,
            routeId VARCHAR(255) NOT NULL,
            seasonId INT NOT NULL,
            stop_points TEXT NOT NULL,
            created TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
            updated TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
            PRIMARY KEY(id)
        );");

        $this->addSql('CREATE INDEX IDX_74D5941377570A4C ON mtt.line_timecard (perimeter_id);');
        $this->addSql('CREATE INDEX IDX_74D5941394D8FDF1 ON mtt.line_timecard (line_config_id);');
        $this->addSql('CREATE INDEX IDX_D29EC30177570A4C ON mtt.timecard (perimeter_id);');
        $this->addSql('CREATE UNIQUE INDEX perimeter_timecard_idx ON mtt.timecard (perimeter_id, seasonId, lineId, routeId);');

        $this->addSql('ALTER TABLE mtt.timecard ADD CONSTRAINT FK_D29EC301DD6FB2B6 FOREIGN KEY (line_timecard_id) REFERENCES mtt.line_timecard (id)');
        $this->addSql('COMMENT ON COLUMN mtt.timecard.stop_points IS \'(DC2Type:array)\';');

        $this->addSql('ALTER TABLE mtt.layout ADD COLUMN "configuration" text;');
        $this->addSql('ALTER TABLE mtt.line_timecard ADD COLUMN hash_pdf VARCHAR(255);');
        $this->addSql('ALTER TABLE mtt.line_timecard ADD pdf_generation_date TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL;');

        $this->addSql('ALTER TABLE mtt.frequency ADD COLUMN filled_column bigint;');

        $this->addSql('ALTER TABLE mtt.block ADD line_timecard_id INT DEFAULT NULL;');
        $this->addSql('ALTER TABLE mtt.block ADD color VARCHAR(255) DEFAULT NULL;');
        $this->addSql('ALTER TABLE mtt.block ADD route VARCHAR(255) DEFAULT NULL;;');
        $this->addSql('ALTER TABLE mtt.block ADD CONSTRAINT FK_3871D76DD6FB2B6 FOREIGN KEY (line_timecard_id) REFERENCES mtt.line_timecard (id) NOT DEFERRABLE INITIALLY IMMEDIATE;');
        $this->addSql('CREATE INDEX IDX_3871D76DD6FB2B6 ON mtt.block (line_timecard_id);');
    }

    public function down(Schema $schema)
    {
    }

    public function getName()
    {
        return self::VERSION;
    }
}
