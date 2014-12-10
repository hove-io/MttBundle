<?php

namespace CanalTP\MttBundle\Migrations\pdo_pgsql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version011 extends AbstractMigration
{
    const VERSION = '0.1.1';

    public function up(Schema $schema)
    {
        $this->addSql('ALTER TABLE mtt.distribution_list RENAME TO __distribution_list;');
    }

    public function down(Schema $schema)
    {
        $this->addSql('ALTER TABLE mtt.__distribution_list RENAME TO distribution_list;');
    }

    public function getName()
    {
        return self::VERSION;
    }
}
