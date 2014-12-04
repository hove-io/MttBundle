<?php

namespace CanalTP\MttBundle\Migrations\pdo_pgsql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version007 extends AbstractMigration
{
    const VERSION = '0.0.7';

    private $lineConfigs;
    private $layoutNetwork;
    private $layouts;
    private $layoutConfigId = 1;

    public function postUp(Schema $schema)
    {
        foreach ($this->layouts as $layout) {
            $statement = $this->connection->prepare('INSERT INTO mtt.layout VALUES (:id, :label, :twig, :preview, 0, \'a:1:{i:0;i:1;}\', 1, \'2014-07-24 18:21:25\', \'2014-07-24 18:21:25\')');
            $statement->bindValue('id', $layout['id']);
            $statement->bindValue('label', $layout['label']);
            $statement->bindValue('twig', $layout['twig']);
            $statement->bindValue('preview', $layout['preview']);
            $statement->execute();

            $statement = $this->connection->prepare('INSERT INTO mtt.layout_config VALUES (:id, :label, 4, 1, 0, NULL, \'2014-07-24 18:21:25\', \'2014-07-24 18:21:25\', :id)');
            $statement->bindValue('id', $layout['id']);
            $statement->bindValue('label', $layout['label']);
            $statement->execute();

        }

        foreach ($this->layoutNetwork as $layoutConfigNetwork) {
            $statement = $this->connection->prepare("INSERT INTO mtt.layout_config_network VALUES (:network_id, :layout_config_id)");
            $statement->bindValue('network_id', $layoutConfigNetwork['network_id']);
            $statement->bindValue('layout_config_id', $layoutConfigNetwork['layout_id']);
            $statement->execute();
        }

        $statement = $this->connection->prepare("CREATE INDEX IDX_37BD935834128B91 ON mtt.layout_config_network (network_id)");
        $statement->execute();
        $statement = $this->connection->prepare("CREATE INDEX IDX_37BD93589C78D002 ON mtt.layout_config_network (layout_config_id)");
        $statement->execute();
        $statement = $this->connection->prepare("ALTER TABLE mtt.layout_config_network ADD CONSTRAINT FK_37BD935834128B91 FOREIGN KEY (network_id) REFERENCES mtt.network (id) NOT DEFERRABLE INITIALLY IMMEDIATE");
        $statement->execute();
        $statement = $this->connection->prepare("ALTER TABLE mtt.layout_config_network ADD CONSTRAINT FK_37BD93589C78D002 FOREIGN KEY (layout_config_id) REFERENCES mtt.layout_config (id) NOT DEFERRABLE INITIALLY IMMEDIATE");
        $statement->execute();
        $statement = $this->connection->prepare("ALTER TABLE mtt.line_config ADD CONSTRAINT FK_DA2EDE8F9C78D002 FOREIGN KEY (layout_config_id) REFERENCES mtt.layout_config (id) NOT DEFERRABLE INITIALLY IMMEDIATE");
        $statement->execute();
        $statement = $this->connection->prepare("CREATE INDEX IDX_DA2EDE8F9C78D002 ON mtt.line_config (layout_config_id)");
        $statement->execute();
    }

    public function preUp(Schema $schema)
    {
        $statement = $this->connection->prepare("SELECT lc.id, lc.layout_id, l.label FROM mtt.line_config AS lc, mtt.layout AS l WHERE lc.layout_id = l.id");
        $statement->execute();
        $this->lineConfigs = $statement->fetchAll();

        $statement = $this->connection->prepare("SELECT ln.network_id, ln.layout_id FROM mtt.layouts_networks AS ln");
        $statement->execute();
        $this->layoutNetwork = $statement->fetchAll();

        $statement = $this->connection->prepare("SELECT l.id, l.label, l.twig, l.preview FROM mtt.layout AS l");
        $statement->execute();
        $this->layouts = $statement->fetchAll();

        $statement = $this->connection->prepare("SELECT (MAX(l.id) + 1) AS start_at FROM mtt.layout AS l");
        $statement->execute();
        $this->layoutConfigId = $statement->fetchAll();
        $this->layoutConfigId[0]['start_at'] = ($this->layoutConfigId[0]['start_at'] == NULL) ? 1 : $this->layoutConfigId[0]['start_at'];
    }

    public function up(Schema $schema)
    {
        // Miration for Area evolution in Mtt app
        $this->addSql('CREATE TABLE mtt.area (
            id SERIAL NOT NULL,
            network_id INT DEFAULT NULL,
            label VARCHAR(255) NOT NULL,
            stop_points TEXT NOT NULL,
            created TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
            updated TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
            PRIMARY KEY(id))'
        );
        $this->addSql('CREATE INDEX IDX_2E79A2FD34128B91 ON mtt.area (network_id)');
        $this->addSql("COMMENT ON COLUMN mtt.area.stop_points IS '(DC2Type:array)'");
        $this->addSql('CREATE UNIQUE INDEX network_area_idx ON mtt.area (label, network_id)');
        $this->addSql('CREATE TABLE mtt.area_pdf (
            id SERIAL NOT NULL,
            area_id INT DEFAULT NULL,
            season_id INT DEFAULT NULL,
            generated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
            created TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
            updated TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
            PRIMARY KEY(id))'
        );
        $this->addSql('CREATE INDEX IDX_CF404A72BD0F409C ON mtt.area_pdf (area_id)');
        $this->addSql('CREATE INDEX IDX_CF404A724EC001D1 ON mtt.area_pdf (season_id)');
        $this->addSql('ALTER TABLE mtt.area ADD CONSTRAINT FK_2E79A2FD34128B91 FOREIGN KEY (network_id) REFERENCES mtt.network (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE mtt.area_pdf ADD CONSTRAINT FK_CF404A72BD0F409C FOREIGN KEY (area_id) REFERENCES mtt.area (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE mtt.area_pdf ADD CONSTRAINT FK_CF404A724EC001D1 FOREIGN KEY (season_id) REFERENCES mtt.season (id) NOT DEFERRABLE INITIALLY IMMEDIATE');

        // Migration about Layout evolution in Mtt app.
        $this->addSql('CREATE SEQUENCE mtt.layout_config_id_seq INCREMENT BY 1 MINVALUE 1 START ' . $this->layoutConfigId[0]['start_at']);
        $this->addSql('CREATE TABLE mtt.layout_config (id SERIAL NOT NULL, label VARCHAR(255) NOT NULL, calendar_start INT NOT NULL, calendar_end INT NOT NULL, notes_mode INT NOT NULL, preview_path VARCHAR(255) DEFAULT NULL, created TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE mtt.layout_config_network (network_id INT NOT NULL, layout_config_id INT NOT NULL, PRIMARY KEY(network_id, layout_config_id))');
        $this->addSql('DROP INDEX mtt.idx_da2ede8f8c22aa1a');
        $this->addSql('ALTER TABLE mtt.line_config RENAME COLUMN layout_id TO layout_config_id');

        $this->addSql('DROP TABLE mtt.layout CASCADE');
        $this->addSql('DROP TABLE mtt.layouts_networks CASCADE');
        $this->addSql('DROP SEQUENCE mtt.layout_id_seq CASCADE');

        $this->addSql('CREATE SEQUENCE mtt.layout_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE mtt.layout (id SERIAL NOT NULL, label VARCHAR(255) NOT NULL, path VARCHAR(255) NOT NULL, preview_path VARCHAR(255) NOT NULL, orientation INT NOT NULL, notes_modes TEXT NOT NULL, css_version INT NOT NULL, created TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql("COMMENT ON COLUMN mtt.layout.notes_modes IS '(DC2Type:array)'");
        $this->addSql('ALTER TABLE mtt.layout_config ADD layout_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE mtt.layout_config ADD CONSTRAINT FK_89FA16908C22AA1A FOREIGN KEY (layout_id) REFERENCES mtt.layout (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_89FA16908C22AA1A ON mtt.layout_config (layout_id)');
    }

    public function down(Schema $schema)
    {
        // Miration for Area evolution in Mtt app
        $this->addSql('DROP TABLE mtt.area CASCADE');
        $this->addSql('DROP TABLE mtt.area_pdf CASCADE');

        // Migration about Layout evolution in Mtt app.
        $this->addSql('DROP TABLE mtt.layout CASCADE');
        $this->addSql('DROP SEQUENCE mtt.layout_id_seq CASCADE');

        $this->addSql('CREATE TABLE mtt.layouts_networks (network_id INT NOT NULL, layout_id INT NOT NULL, PRIMARY KEY(network_id, layout_id))');
        $this->addSql('CREATE INDEX IDX_5C61C70A34128B91 ON mtt.layouts_networks (network_id)');
        $this->addSql('CREATE INDEX IDX_5C61C70A8C22AA1A ON mtt.layouts_networks (layout_id)');
        $this->addSql('CREATE SEQUENCE mtt.layout_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE mtt.layout (
            id SERIAL NOT NULL,
            label character varying(255) NOT NULL,
            twig character varying(255) NOT NULL,
            preview character varying(255) NOT NULL,
            orientation character varying(255) NOT NULL,
            calendar_start integer NOT NULL,
            calendar_end integer NOT NULL,
            created timestamp(0) without time zone NOT NULL,
            updated timestamp(0) without time zone NOT NULL,
            css_version integer DEFAULT 0,
            notes_mode integer DEFAULT 0,
            PRIMARY KEY(id)
        );');
        $this->addSql('INSERT INTO mtt.layout VALUES (1, \'Layout 1 de type paysage (Dijon 1)\', \'layout_1.html.twig\', \'/bundles/canaltpmtt/img/layout_1.png\', \'landscape\', 4, 1, \'2014-07-07 11:30:49\', \'2014-07-07 11:30:49\', 1, 0)');
        $this->addSql('INSERT INTO mtt.layout VALUES (2, \'Layout 2 de type paysage (Dijon 2)\', \'layout_2.html.twig\', \'/bundles/canaltpmtt/img/layout_2.png\', \'landscape\', 4, 1, \'2014-07-07 11:30:49\', \'2014-07-07 11:30:49\', 1, 0)');
        $this->addSql('INSERT INTO mtt.layout VALUES (3, \'Lianes 4 paves neutre\', \'Divia/neutralLianes4Timegrids.html.twig\', \'/bundles/canaltpmtt/img/layouts/divia/neutral-Lianes-4-paves.png\', \'landscape\', 4, 1, \'2014-07-07 11:30:49\', \'2014-07-07 11:30:49\', 1, 0)');
        $this->addSql('INSERT INTO mtt.layout VALUES (4, \'Lianes 4 paves\', \'Divia/lianes4Timegrids.html.twig\', \'/bundles/canaltpmtt/img/layouts/divia/Lianes-4-paves.png\', \'landscape\', 4, 1, \'2014-07-07 11:30:49\', \'2014-07-07 11:30:49\', 1, 0)');
        $this->addSql('INSERT INTO mtt.layout VALUES (5, \'Flexo\', \'Divia/flexo.html.twig\', \'/bundles/canaltpmtt/img/layouts/divia/Flexo.png\', \'landscape\', 4, 1, \'2014-07-07 11:30:49\', \'2014-07-07 11:30:49\', 1, 0)');
        $this->addSql('INSERT INTO mtt.layout VALUES (6, \'Proxi\', \'Divia/proxi.html.twig\', \'/bundles/canaltpmtt/img/layouts/divia/Proxi.png\', \'landscape\', 4, 1, \'2014-07-07 11:30:49\', \'2014-07-07 11:30:49\', 1, 0)');
        $this->addSql('ALTER TABLE mtt.layouts_networks ADD CONSTRAINT FK_5C61C70A34128B91 FOREIGN KEY (network_id) REFERENCES mtt.network (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE mtt.layouts_networks ADD CONSTRAINT FK_5C61C70A8C22AA1A FOREIGN KEY (layout_id) REFERENCES mtt.layout (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE mtt.network ALTER token SET NOT NULL');

        $this->addSql('ALTER TABLE mtt.line_config DROP CONSTRAINT FK_DA2EDE8F9C78D002');
        $this->addSql('DROP INDEX mtt.IDX_DA2EDE8F9C78D002');
        $this->addSql('ALTER TABLE mtt.line_config RENAME COLUMN layout_config_id TO layout_id');
        $this->addSql('ALTER TABLE mtt.line_config ADD CONSTRAINT FK_DA2EDE8F8C22AA1A FOREIGN KEY (layout_id) REFERENCES mtt.layout (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_DA2EDE8F8C22AA1A ON mtt.line_config (layout_id)');
        $this->addSql('DROP TABLE mtt.layout_config CASCADE');
        $this->addSql('DROP TABLE mtt.layout_config_network CASCADE');
        $this->addSql('DROP SEQUENCE mtt.layout_config_id_seq CASCADE');
    }

    public function getName()
    {
        return self::VERSION;
    }
}
