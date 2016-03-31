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

    public function up(Schema $schema)
    {
        $this->addSql('ALTER TABLE mtt.layout ADD page_size VARCHAR(255) DEFAULT \'A4\' NOT NULL;');
    }

    public function down(Schema $schema)
    {
        $this->addSql('ALTER TABLE mtt.layout DROP page_size;');
    }

    public function getName()
    {
        return self::VERSION;
    }
}
