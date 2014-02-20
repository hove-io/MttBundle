<?php
namespace CanalTP\MethBundle\Form\Type\Block;

use Symfony\Component\Form\FormBuilderInterface;
use CanalTP\MethBundle\Form\Type\BlockType;

class TimegridType extends BlockType
{
    private $calendarManager = null;
    private $externalCoverageId = null;
    private $externalRouteId = null;
    
    public function __construct($calendarManager, $instance, $externalCoverageId)
    {
        $this->calendarManager = $calendarManager;
        $this->externalRouteId = $instance->getTimetable()->getExternalRouteId();
        $this->externalCoverageId = $externalCoverageId;
    }
    
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $calendars = $this->calendarManager->getCalendarsForRoute(
            $this->externalCoverageId, 
            $this->externalRouteId
        );

        $choices = array();
        foreach($calendars as $calendar)
        {
            $choices[$calendar->id] = $calendar->name;
        }
        $builder
            ->add('title', 'text')
            ->add(
                'content', 
                'choice', 
                array(
                    'choices'   => $choices,
                    'attr'      => array(
                        'data-fill-title'   => true
                    )
                )
            );
        parent::buildForm($builder, $options);
    }

    public function getName()
    {
        return 'timegrid_block';
    }
}
