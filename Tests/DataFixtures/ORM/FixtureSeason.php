<?php

namespace CanalTP\MttBundle\DataFixtures\ORM;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;

use CanalTP\MttBundle\Entity\Network;
use CanalTP\MttBundle\Entity\Season;
use CanalTP\MttBundle\Entity\LineConfig;


class FixturesSeason extends AbstractFixture implements OrderedFixtureInterface
{

    private function createNetwork(ObjectManager $em)
    {
        $network = new Network();
        $network->setExternalId('network:Filbleu');
        $network->setExternalCoverageId('Centre');

        $em->persist($network);

        return ($network);
    }

    private function createSeason(ObjectManager $em, $network)
    {
        $season = new Season();
        $season->setNetwork($network);
        $season->setTitle('hiver 2015');
        $season->setStartDate(new \DateTime("now"));
        $season->setEndDate(new \DateTime("+6 month"));

        $em->persist($season);

        return ($season);
    }

    private function createLineConfig(ObjectManager $em, $season)
    {
        $lineConfig = new LineConfig();
        $lineConfig->setSeason($season);
        $lineConfig->setLayout('layout_1');
        $lineConfig->setExternalLineId('line:TTR:Nav62');

        $em->persist($lineConfig);

        return ($lineConfig);
    }


    public function load(ObjectManager $em)
    {
        $network = $this->createNetwork($em);
        $season = $this->createSeason($em, $network);
        $lineConfig = $this->createLineConfig($em, $season);
        
        $em->flush();
    }

    /**
    * {@inheritDoc}
    */
    public function getOrder()
    {
        return 1;
    }
}
