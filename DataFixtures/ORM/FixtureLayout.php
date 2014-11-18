<?php

namespace CanalTP\MttBundle\DataFixtures\ORM;

use Doctrine\Common\Persistence\ObjectManager;
use CanalTP\SamEcoreUserManagerBundle\Entity\User;

use CanalTP\SamCoreBundle\Tests\DataFixtures\ORM\Fixture as SamBaseFixture;
use CanalTP\MttBundle\Entity\Network;
use CanalTP\MttBundle\Entity\LayoutConfig;
use CanalTP\MttBundle\Entity\Layout;

class FixtureLayout extends SamBaseFixture
{
	private $om;

    private function createLayout($layoutProperties, $networks = array())
    {
        $layout = new Layout();
        $layout->setLabel($layoutProperties['label']);
        $layout->setPath($layoutProperties['path']);
        $layout->setPreviewPath($layoutProperties['previewPath']);
        $layout->setOrientation($layoutProperties['orientation']);
        $layout->setNotesModes($layoutProperties['notesModes']);
        $layout->setCssVersion($layoutProperties['cssVersion']);

        $this->om->persist($layout);

        return ($layout);
    }

    private function createLayoutConfig($layoutConfigProperties, Layout $layout, $networks = array())
    {
        $layoutConfig = new LayoutConfig();
        $layoutConfig->setLabel($layoutConfigProperties['label']);
        $layoutConfig->setCalendarStart($layoutConfigProperties['calendarStart']);
        $layoutConfig->setCalendarEnd($layoutConfigProperties['calendarEnd']);
        $layoutConfig->setNotesMode($layoutConfigProperties['notesMode']);
        $layoutConfig->setLayout($layout);

        $this->om->persist($layoutConfig);

        return ($layoutConfig);
    }

    private function createLayouts()
    {
        $this->createLayoutConfig(
            array(
                'label' => 'Template par défaut',
                'calendarStart' => 5,
                'calendarEnd' => 22,
                'notesMode' => 1
            ),
            $this->createLayout(
                array(
                    'label'         => 'Template par défaut',
                    'path'          => 'default.html.twig',
                    'previewPath'   => '/bundles/canaltpmtt/img/default.png',
                    'orientation'   => Layout::ORIENTATION_LANDSCAPE,
                    'notesModes'    => array(LayoutConfig::NOTES_MODE_DISPATCHED),
                    'cssVersion'    => 1
                )
            )
        );

        $this->om->flush();
    }

    public function load(ObjectManager $om)
    {
        $this->om = $om;

        $this->createLayouts();
    }

    /**
    * {@inheritDoc}
    */
    public function getOrder()
    {
        return 4;
    }
}
