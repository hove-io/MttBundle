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

    public function up(Schema $schema)
    {
        $this->addSql('ALTER TABLE mtt.season DROP CONSTRAINT fk_9c6252ce34128b91;');
        $this->addSql('ALTER TABLE mtt.season RENAME network_id TO perimeter_id;');

        $this->addSql('ALTER TABLE mtt.area DROP CONSTRAINT fk_2e79a2fd34128b91;');
        $this->addSql('ALTER TABLE mtt.area RENAME network_id TO perimeter_id;');

        // $this->addSql('ALTER TABLE mtt.amqp_task DROP CONSTRAINT fk_2e79a2fd34128b91;');
        $this->addSql('ALTER TABLE mtt.amqp_task RENAME network_id TO perimeter_id;');
//        Need to fix foreign key constraint between season and perimeter before execute this
//        $this->addSql('ALTER TABLE mtt.season ADD CONSTRAINT fk_season_perimeters FOREIGN KEY (perimeter_id)
//            REFERENCES public.t_perimeter_per(per_id) MATCH SIMPLE ON UPDATE NO ACTION ON DELETE NO ACTION;');

        $this->addSql('CREATE SEQUENCE mtt.layout_config_customer_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE mtt.layout_config_customer (id INT NOT NULL, layout_config_id INT DEFAULT NULL, customer_id INT DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_2609917C9C78D002 ON mtt.layout_config_customer (layout_config_id)');
        $this->addSql('CREATE INDEX IDX_2609917C9395C3F3 ON mtt.layout_config_customer (customer_id)');
        $this->addSql('ALTER TABLE mtt.layout_config_customer ADD CONSTRAINT FK_2609917C9C78D002 FOREIGN KEY (layout_config_id) REFERENCES mtt.layout_config (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE mtt.layout_config_customer ADD CONSTRAINT FK_2609917C9395C3F3 FOREIGN KEY (customer_id) REFERENCES public.tr_customer_cus (cus_id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema)
    {
        $this->addSql('ALTER TABLE mtt.season DROP CONSTRAINT fk_season_perimeters');
        $this->addSql('ALTER TABLE mtt.season RENAME perimeter_id TO network_id;');
//        $this->addSql('ALTER TABLE mtt.season ADD CONSTRAINT fk_9c6252ce34128b91 FOREIGN KEY (network_id)
//            REFERENCES mtt.network (id) MATCH SIMPLE ON UPDATE NO ACTION ON DELETE NO ACTION;');

        // $this->addSql('DROP SEQUENCE mtt.layout_config_customer_id_seq;');
        // $this->addSql('ALTER TABLE mtt.layout_config_customer DROP CONSTRAINT FK_2609917C9C78D002;');
        // $this->addSql('ALTER TABLE mtt.layout_config_customer DROP CONSTRAINT FK_2609917C9395C3F3;');
        // $this->addSql('DROP TABLE mtt.layout_config_customer;');
    }

    public function getName()
    {
        return self::VERSION;
    }
}
