<?php

namespace CanalTP\MttBundle\Migrations\pdo_pgsql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version010 extends AbstractMigration
{
    const VERSION = '0.1.0';

    public function up(Schema $schema)
    {
        $this->addSql('ALTER TABLE mtt.area_pdf ALTER generated_at DROP NOT NULL;');
    }

    public function down(Schema $schema)
    {
        $this->addSql('ALTER TABLE mtt.area_pdf ALTER generated_at SET NOT NULL;');
    }

    public function getName()
    {
        return self::VERSION;
    }
}
