<?php
namespace CanalTP\MethBundle\Form\Type\Block;

use Symfony\Component\Form\FormBuilderInterface;
use CanalTP\MethBundle\Form\Type\BlockType;

class TimegridType extends BlockType
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
        
        $builder
            ->add('title', 'text')
            ->add(
                'content', 
                'choice', 
                array(
                    'choices'   => $this->getChoices($calendars),
                    'attr'      => array(
                        // attribute to tell javascript to fill title when a change occurs on this field
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
        // TODO Specifications not clear enough
        // retrieve other blocks on this timetable
        $blocks = $this->blockInstance->getTimetable()->getBlocks();
        // keep only timegrid blocks
        $usedCalendars = array();
        for ($i = 0; $i < count($blocks);$i++)
        {
            if ($blocks[$i]->getTypeId() == 'timegrid')
            {
                $usedCalendars[] = $blocks[$i]->getContent();
            }
            // echo '<br/>';
        }
        // var_dump(count($blocks), $blocks->count());
        // die;
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
        return 'timegrid_block';
    }
}
