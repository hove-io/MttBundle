<?php

namespace CanalTP\MttBundle\Migrations\pdo_pgsql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version015 extends AbstractMigration
{
    const VERSION = '0.1.5';

    public function up(Schema $schema)
    {
        $this->addSql('ALTER TABLE mtt.block DROP COLUMN stop_point_id;');
    }

    public function down(Schema $schema)
    {
        $this->addSql('ALTER TABLE mtt.block ADD COLUMN stop_point_id INT DEFAULT NULL REFERENCES mtt.stop_point(id) NOT DEFERRABLE INITIALLY IMMEDIATE;');
        $this->addSql('CREATE INDEX block_stop_point_id_idx ON mtt.block (stop_point_id)');
    }

    public function getName()
    {
        return self::VERSION;
    }
}
