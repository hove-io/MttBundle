<?php

namespace CanalTP\MttBundle\Migrations\pdo_pgsql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version013 extends AbstractMigration
{
    const VERSION = '0.1.3';

    public function up(Schema $schema)
    {
        $this->addSql("CREATE TABLE mtt.calendar (
            id SERIAL NOT NULL, 
            title VARCHAR(255) NOT NULL, 
            start_date TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
            end_date TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
            weekly_pattern CHAR(7) NOT NULL,
            customer_id INTEGER NOT NULL REFERENCES public.tr_customer_cus(cus_id),
            PRIMARY KEY(id));
         ");
    }

    public function down(Schema $schema)
    {
        $this->addSql('DROP TABLE mtt.calendar;');
    }

    public function getName()
    {
        return self::VERSION;
    }
}
