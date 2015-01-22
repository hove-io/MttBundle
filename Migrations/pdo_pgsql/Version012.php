<?php

namespace CanalTP\MttBundle\Migrations\pdo_pgsql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use CanalTP\MttBundle\Entity\AmqpTask;

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

        $this->addSql("CREATE TABLE mtt.timecard_pdf (
            id SERIAL NOT NULL,
            timecard_id INT DEFAULT NULL,
            season_id INT DEFAULT NULL,
            generated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
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
        $this->addSql('CREATE INDEX IDX_EFD19966EA093255 ON mtt.timecard_pdf (timecard_id);');
        $this->addSql('CREATE INDEX IDX_EFD199664EC001D1 ON mtt.timecard_pdf (season_id);');

        $this->addSql('ALTER TABLE mtt.layout ADD COLUMN "configuration" text;');
    }

    public function down(Schema $schema)
    {
    }

    public function getName()
    {
        return self::VERSION;
    }
}
