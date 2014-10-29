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
//        Need to fix foreign key constraint between season and perimeter before execute this
//        $this->addSql('ALTER TABLE mtt.season ADD CONSTRAINT fk_season_perimeters FOREIGN KEY (perimeter_id)
//            REFERENCES public.t_perimeter_per(per_id) MATCH SIMPLE ON UPDATE NO ACTION ON DELETE NO ACTION;');
    }

    public function down(Schema $schema)
    {
        $this->addSql('ALTER TABLE mtt.season DROP CONSTRAINT fk_season_perimeters');
        $this->addSql('ALTER TABLE mtt.season RENAME perimeter_id TO network_id;');
//        $this->addSql('ALTER TABLE mtt.season ADD CONSTRAINT fk_9c6252ce34128b91 FOREIGN KEY (network_id)
//            REFERENCES mtt.network (id) MATCH SIMPLE ON UPDATE NO ACTION ON DELETE NO ACTION;');
    }

    public function getName()
    {
        return self::VERSION;
    }
}
