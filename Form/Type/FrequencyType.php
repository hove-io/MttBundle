<?php
namespace CanalTP\MttBundle\Form\Type;

use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\AbstractType;
use CanalTP\MttBundle\Twig\CalendarExtension;

class FrequencyType extends AbstractType
{
    private $hoursRange;

    public function __construct($layoutConfig, $action)
    {
        $calendarRange = $layoutConfig['calendar_range'];
        $extension = new CalendarExtension();
        $this->hoursRange = $extension->calendarRange($calendarRange);
        $this->action = $action;
    }
    
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('startTime', 'time', array(
            'with_minutes'  => false,
            'hours' => $this->hoursRange
        ));
        array_shift($this->hoursRange);
        $builder->add('endTime', 'time', array(
            'with_minutes'  => false,
            'hours' => $this->hoursRange
        ));
        $builder->add('content', 'textarea');
        $builder->setAction($this->action);
    }
    
    public function getName()
    {
        return 'frequency';
    }
}