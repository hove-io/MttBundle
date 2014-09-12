<?php

namespace CanalTP\MttBundle\Migrations\pdo_pgsql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version001 extends AbstractMigration
{
    const VERSION = '0.0.1';

    public function getName()
    {
        return self::VERSION;
    }

    public function up(Schema $schema)
    {
        $this->addSql('CREATE SCHEMA mtt;');
        $this->addSql('CREATE TABLE mtt.timetable (id INT NOT NULL, line_config_id INT DEFAULT NULL, external_route_id VARCHAR(255) NOT NULL, created TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_9E30B6E594D8FDF1 ON mtt.timetable (line_config_id)');
        $this->addSql('CREATE UNIQUE INDEX timetable_external_route_idx ON mtt.timetable (line_config_id, external_route_id)');
        $this->addSql('CREATE TABLE mtt.block (id SERIAL NOT NULL, timetable_id INT DEFAULT NULL, stop_point_id INT DEFAULT NULL, type_id VARCHAR(255) NOT NULL, dom_id VARCHAR(128) NOT NULL, content TEXT DEFAULT NULL, title VARCHAR(255) NOT NULL, created TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_3871D76CC306847 ON mtt.block (timetable_id)');
        $this->addSql('CREATE INDEX IDX_3871D7612829449 ON mtt.block (stop_point_id)');
        $this->addSql('CREATE TABLE mtt.season (id SERIAL NOT NULL, network_id INT DEFAULT NULL, title VARCHAR(255) NOT NULL, start_date DATE NOT NULL, end_date DATE NOT NULL, created TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_9C6252CE34128B91 ON mtt.season (network_id)');
        $this->addSql('CREATE UNIQUE INDEX network_season_idx ON mtt.season (title, network_id)');
        $this->addSql('CREATE TABLE mtt.frequency (id SERIAL NOT NULL, block_id INT DEFAULT NULL, start_time TIME(0) WITHOUT TIME ZONE NOT NULL, end_time TIME(0) WITHOUT TIME ZONE NOT NULL, content TEXT NOT NULL, created TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_BEFEF886E9ED820C ON mtt.frequency (block_id)');
        $this->addSql('CREATE TABLE mtt.network (id SERIAL NOT NULL, external_id VARCHAR(255) NOT NULL, external_coverage_id VARCHAR(255) NOT NULL, created TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE mtt.users_networks (network_id INT NOT NULL, user_id INT NOT NULL, PRIMARY KEY(network_id, user_id))');
        $this->addSql('CREATE INDEX IDX_79651E0934128B91 ON mtt.users_networks (network_id)');
        $this->addSql('CREATE INDEX IDX_79651E09A76ED395 ON mtt.users_networks (user_id)');
        $this->addSql('CREATE TABLE mtt.layouts_networks (network_id INT NOT NULL, layout_id INT NOT NULL, PRIMARY KEY(network_id, layout_id))');
        $this->addSql('CREATE INDEX IDX_5C61C70A34128B91 ON mtt.layouts_networks (network_id)');
        $this->addSql('CREATE INDEX IDX_5C61C70A8C22AA1A ON mtt.layouts_networks (layout_id)');
        $this->addSql('CREATE TABLE mtt.layout (id INT NOT NULL, label VARCHAR(255) NOT NULL, twig VARCHAR(255) NOT NULL, preview VARCHAR(255) NOT NULL, orientation VARCHAR(255) NOT NULL, calendar_start INT NOT NULL, calendar_end INT NOT NULL, created TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE mtt.line_config (id SERIAL NOT NULL, layout_id INT DEFAULT NULL, season_id INT DEFAULT NULL, external_line_id VARCHAR(255) NOT NULL, created TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_DA2EDE8F8C22AA1A ON mtt.line_config (layout_id)');
        $this->addSql('CREATE INDEX IDX_DA2EDE8F4EC001D1 ON mtt.line_config (season_id)');
        $this->addSql('CREATE UNIQUE INDEX season_exernal_line_idx ON mtt.line_config (season_id, external_line_id)');
        $this->addSql('CREATE TABLE mtt.stop_point (id SERIAL NOT NULL, timetable_id INT DEFAULT NULL, external_id VARCHAR(255) NOT NULL, pdf_generation_date TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, created TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_BABAFE98CC306847 ON mtt.stop_point (timetable_id)');
        $this->addSql('CREATE UNIQUE INDEX stop_point_timetable_idx ON mtt.stop_point (timetable_id, external_id)');
        $this->addSql('CREATE TABLE mtt.distribution_list (id SERIAL NOT NULL, network_id INT DEFAULT NULL, external_route_id VARCHAR(255) NOT NULL, included_stops TEXT NOT NULL, created TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_5B8410EC34128B91 ON mtt.distribution_list (network_id)');
        $this->addSql('CREATE UNIQUE INDEX network_external_route_idx ON mtt.distribution_list (network_id, external_route_id)');
        $this->addSql('COMMENT ON COLUMN mtt.distribution_list.included_stops IS \'(DC2Type:array)\'');

        $this->addSql('CREATE SEQUENCE mtt.timetable_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE mtt.layout_id_seq INCREMENT BY 1 MINVALUE 1 START 1');

        $this->addSql('ALTER TABLE mtt.timetable ADD CONSTRAINT FK_9E30B6E594D8FDF1 FOREIGN KEY (line_config_id) REFERENCES mtt.line_config (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE mtt.block ADD CONSTRAINT FK_3871D76CC306847 FOREIGN KEY (timetable_id) REFERENCES mtt.timetable (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE mtt.block ADD CONSTRAINT FK_3871D7612829449 FOREIGN KEY (stop_point_id) REFERENCES mtt.stop_point (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE mtt.season ADD CONSTRAINT FK_9C6252CE34128B91 FOREIGN KEY (network_id) REFERENCES mtt.network (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE mtt.frequency ADD CONSTRAINT FK_BEFEF886E9ED820C FOREIGN KEY (block_id) REFERENCES mtt.block (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE mtt.users_networks ADD CONSTRAINT FK_79651E0934128B91 FOREIGN KEY (network_id) REFERENCES mtt.network (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE mtt.users_networks ADD CONSTRAINT FK_79651E09A76ED395 FOREIGN KEY (user_id) REFERENCES t_user_usr (usr_id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE mtt.layouts_networks ADD CONSTRAINT FK_5C61C70A34128B91 FOREIGN KEY (network_id) REFERENCES mtt.network (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE mtt.layouts_networks ADD CONSTRAINT FK_5C61C70A8C22AA1A FOREIGN KEY (layout_id) REFERENCES mtt.layout (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE mtt.line_config ADD CONSTRAINT FK_DA2EDE8F8C22AA1A FOREIGN KEY (layout_id) REFERENCES mtt.layout (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE mtt.line_config ADD CONSTRAINT FK_DA2EDE8F4EC001D1 FOREIGN KEY (season_id) REFERENCES mtt.season (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE mtt.stop_point ADD CONSTRAINT FK_BABAFE98CC306847 FOREIGN KEY (timetable_id) REFERENCES mtt.timetable (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE mtt.distribution_list ADD CONSTRAINT FK_5B8410EC34128B91 FOREIGN KEY (network_id) REFERENCES mtt.network (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema)
    {
        $this->addSql('DROP SCHEMA mtt;');
    }
}
