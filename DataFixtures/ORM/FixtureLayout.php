<?php

namespace CanalTP\MttBundle\DataFixtures\ORM;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;

use CanalTP\MttBundle\Entity\LayoutCustomer;
use CanalTP\MttBundle\Entity\LayoutConfig;
use CanalTP\MttBundle\Entity\Layout;

class FixtureLayout extends AbstractFixture implements OrderedFixtureInterface
{
    private $om;

    private function createLayout($layoutProperties, $networks = array())
    {
        $layout = new Layout();
        $layout->setLabel($layoutProperties['label']);
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
        $layoutConfig->setNotesType($layoutConfigProperties['notesType']);
        $layoutConfig->setNotesColors(array(
            '#e44155',
            '#ff794e',
            '#4460c5',
            '#0cc2dd',
            '#6ebf52',
            '#bacd40'));
        $layoutConfig->setLayout($layout);

        $this->om->persist($layoutConfig);

        return ($layoutConfig);
    }

    private function createLayouts()
    {
        $defaultStopLayout = $this->createLayout(
            array(
                'label'         => 'Template arrêt par défaut',
                'previewPath'   => '/bundles/canaltpmtt/img/default-stop-layout.png',
                'orientation'   => Layout::ORIENTATION_LANDSCAPE,
                'notesModes'    => array(LayoutConfig::NOTES_MODE_DISPATCHED),
                'cssVersion'    => 1
            )
        );
        $this->attachToCustomerCtp($defaultStopLayout);
        $this->addReference('default-stop-layout', $defaultStopLayout);

        $colorStopLayout = $this->createLayout(
            array(
                'label'         => 'Template arrêt couleur',
                'previewPath'   => '/bundles/canaltpmtt/img/color-stop-layout.png',
                'orientation'   => Layout::ORIENTATION_LANDSCAPE,
                'notesModes'    => array(LayoutConfig::NOTES_MODE_DISPATCHED),
                'cssVersion'    => 1
            )
        );
        $this->attachToCustomerCtp($colorStopLayout);
        $this->addReference('color-stop-layout', $colorStopLayout);

        $layoutConfig = $this->createLayoutConfig(
            array(
                'label'         => 'Template arrêt par défaut (exposant)',
                'calendarStart' => 5,
                'calendarEnd'   => 22,
                'notesMode'     => 1,
                'notesType'     => LayoutConfig::NOTES_TYPE_EXPONENT
            ),
            $defaultStopLayout
        );

        $layoutConfig = $this->createLayoutConfig(
            array(
                'label'         => 'Template arrêt par défaut (color)',
                'calendarStart' => 5,
                'calendarEnd'   => 22,
                'notesMode'     => 1,
                'notesType'     => LayoutConfig::NOTES_TYPE_COLOR
            ),
            $colorStopLayout
        );
    }

    public function load(ObjectManager $om)
    {
        $this->om = $om;

        $this->createLayouts();
        $this->om->flush();
    }

    protected function attachToCustomerCtp($layout)
    {
        $layoutCustomer = new LayoutCustomer();
        $layoutCustomer->setCustomer($this->getReference('customer-canaltp'));
        $layoutCustomer->setLayout($layout);

        $this->om->persist($layoutCustomer);
        $this->om->flush($layoutCustomer);
    }

    /**
    * {@inheritDoc}
    */
    public function getOrder()
    {
        return 4;
    }
}
