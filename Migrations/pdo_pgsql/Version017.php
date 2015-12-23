<?php

namespace CanalTP\MttBundle\Migrations\pdo_pgsql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version017 extends AbstractMigration
{
    const VERSION = '0.1.7';

    public function up(Schema $schema)
    {
        $this->addSql('ALTER TABLE mtt.block RENAME COLUMN type_id TO type;');
        $this->addSql('ALTER TABLE mtt.block ALTER column dom_id DROP NOT NULL;');
    }

    public function down(Schema $schema)
    {
        $this->addSql('ALTER TABLE mtt.block RENAME COLUMN type TO type_id;');
        $this->addSql('UPDATE mtt.block SET dom_id = \'\' WHERE dom_id IS NULL;');
        $this->addSql('ALTER TABLE mtt.block ALTER column dom_id SET NOT NULL;');
    }

    public function getName()
    {
        return self::VERSION;
    }
}
