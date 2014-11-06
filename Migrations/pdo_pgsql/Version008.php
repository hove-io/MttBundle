<?php

namespace CanalTP\MttBundle\Migrations\pdo_pgsql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version008 extends AbstractMigration
{
    const VERSION = '0.0.8';
    private $distributionLists;
    private $amqpTasks;
    private $networks;

    public function up(Schema $schema)
    {
        $this->addSql('ALTER TABLE mtt.season DROP CONSTRAINT fk_9c6252ce34128b91;');
        $this->addSql('ALTER TABLE mtt.season RENAME network_id TO customer_id;');

        $this->addSql('ALTER TABLE mtt.area DROP CONSTRAINT fk_2e79a2fd34128b91;');
        $this->addSql('ALTER TABLE mtt.area RENAME network_id TO customer_id;');

        $this->addSql('ALTER TABLE mtt.amqp_task DROP CONSTRAINT fk_f63afecb34128b91');
        $this->addSql('ALTER TABLE mtt.amqp_task RENAME network_id TO customer_id;');

        $this->addSql('ALTER TABLE mtt.distribution_list RENAME COLUMN network_id TO perimeter_id');
        $this->addSql('ALTER TABLE mtt.distribution_list DROP CONSTRAINT fk_5b8410ec34128b91');
        $this->addSql('ALTER TABLE mtt.distribution_list ADD CONSTRAINT FK_5B8410EC77570A4C FOREIGN KEY (perimeter_id) REFERENCES public.t_perimeter_per (per_id) NOT DEFERRABLE INITIALLY IMMEDIATE');

        $this->addSql('CREATE INDEX IDX_5B8410EC77570A4C ON mtt.distribution_list (perimeter_id)');
        $this->addSql('CREATE UNIQUE INDEX perimeter_external_route_idx ON mtt.distribution_list (perimeter_id, external_route_id)');
        $this->addSql('ALTER TABLE mtt.layout_config ALTER id DROP DEFAULT');
        $this->addSql('ALTER TABLE mtt.season ALTER published DROP DEFAULT');
        $this->addSql('ALTER TABLE mtt.season ALTER published SET NOT NULL');
        $this->addSql('ALTER TABLE mtt.season ALTER locked DROP DEFAULT');
        $this->addSql('ALTER TABLE mtt.season ALTER locked SET NOT NULL');
        $this->addSql('ALTER TABLE mtt.season RENAME COLUMN customer_id TO perimeter_id');
        $this->addSql('ALTER TABLE mtt.season ADD CONSTRAINT FK_9C6252CE77570A4C FOREIGN KEY (perimeter_id) REFERENCES public.t_perimeter_per (per_id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_9C6252CE77570A4C ON mtt.season (perimeter_id)');
        $this->addSql('CREATE UNIQUE INDEX perimeter_season_idx ON mtt.season (title, perimeter_id)');
        $this->addSql('ALTER TABLE mtt.area RENAME COLUMN customer_id TO perimeter_id');
        $this->addSql('ALTER TABLE mtt.area ADD CONSTRAINT FK_2E79A2FD77570A4C FOREIGN KEY (perimeter_id) REFERENCES public.t_perimeter_per (per_id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_2E79A2FD77570A4C ON mtt.area (perimeter_id)');
        $this->addSql('CREATE UNIQUE INDEX perimeter_area_idx ON mtt.area (label, perimeter_id)');
        $this->addSql('ALTER TABLE mtt.amqp_task RENAME COLUMN customer_id TO perimeter_id');
        $this->addSql('ALTER TABLE mtt.amqp_task ADD CONSTRAINT FK_F63AFECB77570A4C FOREIGN KEY (perimeter_id) REFERENCES public.t_perimeter_per (per_id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_F63AFECB77570A4C ON mtt.amqp_task (perimeter_id)');
        $this->addSql('ALTER TABLE mtt.layout ALTER id DROP DEFAULT');

        // TODO: Do migration for network to perimeter
        $this->addSql('ALTER TABLE mtt.network RENAME TO __network;');
        $this->addSql('ALTER TABLE mtt.users_networks RENAME TO __users_networks;');
        $this->addSql('ALTER TABLE mtt.layout_config_network RENAME TO __layout_config_network;');

        $this->addSql('CREATE SEQUENCE mtt.layout_customer_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE mtt.layout_customer (id INT NOT NULL, layout_id INT DEFAULT NULL, customer_id INT DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_2609917C9C78D002 ON mtt.layout_customer (layout_id)');
        $this->addSql('CREATE INDEX IDX_2609917C9395C3F3 ON mtt.layout_customer (customer_id)');
        $this->addSql('ALTER TABLE mtt.layout_customer ADD CONSTRAINT FK_2609917C9C78D002 FOREIGN KEY (layout_id) REFERENCES mtt.layout (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE mtt.layout_customer ADD CONSTRAINT FK_2609917C9395C3F3 FOREIGN KEY (customer_id) REFERENCES public.tr_customer_cus (cus_id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema)
    {
        $this->addSql('ALTER TABLE mtt.season DROP CONSTRAINT fk_season_perimeters');
        $this->addSql('ALTER TABLE mtt.season RENAME perimeter_id TO network_id;');

        // TODO: Migration down (migration to network)
        $this->addSql('ALTER TABLE mtt.__network RENAME TO network;');
        $this->addSql('ALTER TABLE mtt.__users_networks RENAME TO users_networks;');
        $this->addSql('ALTER TABLE "mtt.__layout_config_network" RENAME TO layout_customer;');
    }

    public function getName()
    {
        return self::VERSION;
    }
}
