<?php

namespace CanalTP\MttBundle\Migrations\pdo_pgsql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version014 extends AbstractMigration
{
    const VERSION = '0.1.4';

    private function updateConstraints($addDeleteCascade)
    {
        $sqlEnding = $addDeleteCascade ? 'ON DELETE CASCADE;' : ';';
        $this->addSql('ALTER TABLE mtt.calendar DROP CONSTRAINT calendar_customer_id_fkey,
            ADD CONSTRAINT calendar_customer_id_fkey
            FOREIGN KEY (customer_id)
            REFERENCES public.tr_customer_cus(cus_id)
        ' . $sqlEnding);
        $this->addSql('ALTER TABLE mtt.layout_customer DROP CONSTRAINT fk_2609917c9395c3f3,
            ADD CONSTRAINT fk_2609917c9395c3f3
            FOREIGN KEY (customer_id)
            REFERENCES public.tr_customer_cus(cus_id)
        ' . $sqlEnding);
        $this->addSql('ALTER TABLE mtt.amqp_ack DROP CONSTRAINT fk_b7057bd726962fd6,
            ADD CONSTRAINT fk_b7057bd726962fd6
            FOREIGN KEY (amqp_task_id)
            REFERENCES mtt.amqp_task(id)
        ' . $sqlEnding);
        $this->addSql('ALTER TABLE mtt.amqp_task DROP CONSTRAINT fk_f63afecb77570a4c,
            ADD CONSTRAINT fk_f63afecb77570a4c
            FOREIGN KEY (perimeter_id)
            REFERENCES public.t_perimeter_per(per_id)
        ' . $sqlEnding);
        $this->addSql('ALTER TABLE mtt.season DROP CONSTRAINT fk_9c6252ce77570a4c,
            ADD CONSTRAINT fk_9c6252ce77570a4c
            FOREIGN KEY (perimeter_id)
            REFERENCES public.t_perimeter_per(per_id)
        ' . $sqlEnding);
        $this->addSql('ALTER TABLE mtt.area_pdf DROP CONSTRAINT fk_cf404a72bd0f409c,
            ADD CONSTRAINT fk_cf404a72bd0f409c
            FOREIGN KEY (area_id)
            REFERENCES mtt.area(id)
        ' . $sqlEnding);
        $this->addSql('ALTER TABLE mtt.area_pdf DROP CONSTRAINT fk_cf404a724ec001d1,
            ADD CONSTRAINT fk_cf404a724ec001d1
            FOREIGN KEY (season_id)
            REFERENCES mtt.season(id)
        ' . $sqlEnding);
        $this->addSql('ALTER TABLE mtt.area DROP CONSTRAINT fk_2e79a2fd77570a4c,
            ADD CONSTRAINT fk_2e79a2fd77570a4c
            FOREIGN KEY (perimeter_id)
            REFERENCES public.t_perimeter_per(per_id)
        ' . $sqlEnding);
        $this->addSql('ALTER TABLE mtt.frequency DROP CONSTRAINT fk_befef886e9ed820c,
            ADD CONSTRAINT fk_befef886e9ed820c
            FOREIGN KEY (block_id)
            REFERENCES mtt.block(id)
        ' . $sqlEnding);
        $this->addSql('ALTER TABLE mtt.block DROP CONSTRAINT fk_3871d76cc306847,
            ADD CONSTRAINT fk_3871d76cc306847
            FOREIGN KEY (timetable_id)
            REFERENCES mtt.timetable(id)
        ' . $sqlEnding);
        $this->addSql('ALTER TABLE mtt.block DROP CONSTRAINT fk_3871d7612829449,
            ADD CONSTRAINT fk_3871d7612829449
            FOREIGN KEY (stop_point_id)
            REFERENCES mtt.stop_point(id)
        ' . $sqlEnding);
        $this->addSql('ALTER TABLE mtt.stop_point DROP CONSTRAINT fk_babafe98cc306847,
            ADD CONSTRAINT fk_babafe98cc306847
            FOREIGN KEY (timetable_id)
            REFERENCES mtt.timetable(id)
        ' . $sqlEnding);
        $this->addSql('ALTER TABLE mtt.timetable DROP CONSTRAINT fk_9e30b6e594d8fdf1,
            ADD CONSTRAINT fk_9e30b6e594d8fdf1
            FOREIGN KEY (line_config_id)
            REFERENCES mtt.line_config(id)
        ' . $sqlEnding);
        $this->addSql('ALTER TABLE mtt.line_config DROP CONSTRAINT fk_da2ede8f9c78d002,
            ADD CONSTRAINT fk_da2ede8f9c78d002
            FOREIGN KEY (layout_config_id)
            REFERENCES mtt.layout_config(id)
        ' . $sqlEnding);
        $this->addSql('ALTER TABLE mtt.line_config DROP CONSTRAINT fk_da2ede8f4ec001d1,
            ADD CONSTRAINT fk_da2ede8f4ec001d1
            FOREIGN KEY (season_id)
            REFERENCES mtt.season(id)
        ' . $sqlEnding);
        $this->addSql('ALTER TABLE mtt.layout_config DROP CONSTRAINT fk_89fa16908c22aa1a,
            ADD CONSTRAINT fk_89fa16908c22aa1a
            FOREIGN KEY (layout_id)
            REFERENCES mtt.layout(id)
        ' . $sqlEnding);
        $this->addSql('ALTER TABLE mtt.layout_customer DROP CONSTRAINT fk_2609917c9c78d002,
            ADD CONSTRAINT fk_2609917c9c78d002
            FOREIGN KEY (layout_id)
            REFERENCES mtt.layout(id)
        ' . $sqlEnding);
        $this->addSql('ALTER TABLE mtt.layout_customer DROP CONSTRAINT fk_2609917c9395c3f3,
            ADD CONSTRAINT fk_2609917c9395c3f3
            FOREIGN KEY (customer_id)
            REFERENCES public.tr_customer_cus(cus_id)
        ' . $sqlEnding);
    }

    public function up(Schema $schema)
    {
        // Delete old tables (clean bdd)
        $this->addSql('DROP TABLE mtt.__distribution_list CASCADE');
        $this->addSql('DROP TABLE mtt.__layout_config_network CASCADE');
        $this->addSql('DROP TABLE mtt.__users_networks CASCADE');
        $this->addSql('DROP TABLE mtt.__network CASCADE');
        // Update constraints
        $this->updateConstraints(true);
    }

    public function down(Schema $schema)
    {
        $this->updateConstraints(false);
    }

    public function getName()
    {
        return self::VERSION;
    }
}
