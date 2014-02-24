<?php
namespace CanalTP\MethBundle\Form\Type\Block;

use Symfony\Component\Form\FormBuilderInterface;
use CanalTP\MethBundle\Form\Type\BlockType;

class CalendarType extends BlockType
{
    private $calendarManager = null;
    private $externalCoverageId = null;
    private $blockInstance = null;
    
    public function __construct($calendarManager, $instance, $externalCoverageId)
    {
        $this->calendarManager = $calendarManager;
        $this->blockInstance = $instance;
        $this->externalCoverageId = $externalCoverageId;
    }
    
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $calendars = $this->calendarManager->getCalendarsForRoute(
            $this->externalCoverageId,
            $this->blockInstance->getTimetable()->getExternalRouteId()
        );
        $choices = $this->getChoices($calendars);
        $builder
            ->add('title', 'text')
            ->add(
                'content', 
                'choice', 
                array(
                    'choices'   => $choices,
                    'attr'      => array(
                        // attribute to tell javascript to fill automatically title 
                        // when a change occurs on this field
                        'data-fill-title'   => true
                    )
                )
            );
        parent::buildForm($builder, $options);
    }
    
    /*
     * @function filter calendars and remove already used calendars by others in the parent timetable
     */
    private function getChoices($calendars)
    {
        // retrieve other blocks on this timetable
        $blocks = $this->blockInstance->getTimetable()->getBlocks();
        // keep only calendar blocks
        $usedCalendars = array();
        for ($i = 0; $i < count($blocks);$i++)
        {
            if ($blocks[$i]->getTypeId() == 'calendar')
            {
                $usedCalendars[] = $blocks[$i]->getContent();
            }
        }
        $choices = array();
        foreach($calendars as $calendar){
            foreach ($blocks as $block)
            {
                if (!in_array($calendar->id, $usedCalendars)) {
                    $choices[$calendar->id] = $calendar->name;
                }
            }
        }
        return $choices;
    }
    
    public function getName()
    {
        return 'calendar_block';
    }
}
