<?php

namespace CanalTP\MttBundle\DataFixtures\ORM;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;

use CanalTP\MttBundle\Entity\Template;
use CanalTP\MttBundle\Entity\Layout;

class FixtureTemplate extends AbstractFixture implements OrderedFixtureInterface
{
    private $om;

    private function createTemplate($templateProperties)
    {
        $template = new Template();
        $template->setType($templateProperties['type']);
        $template->setPath($templateProperties['path']);

        $this->om->persist($template);

        return ($template);
    }

    public function load(ObjectManager $om)
    {
        $this->om = $om;

        $defaultStopLayout = $this->getReference('default-stop-layout');
        $defaultStopLayout->addTemplate(
            $this->createTemplate(
                array(
                    'type'          => 'stop',
                    'path'          => 'default-stop-layout.html.twig'
                )
            )
        );
        $this->om->persist($defaultStopLayout);

        $colorStopLayout = $this->getReference('color-stop-layout');
        $colorStopLayout->addTemplate(
            $this->createTemplate(
                array(
                    'type'          => 'stop',
                    'path'          => 'color-stop-layout.html.twig'
                )
            )
        );
        $this->om->persist($colorStopLayout);

        $this->om->flush();
    }

    /**
    * {@inheritDoc}
    */
    public function getOrder()
    {
        return 5;
    }
}
