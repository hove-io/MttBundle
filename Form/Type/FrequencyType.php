<?php
namespace CanalTP\MttBundle\Form\Type;

use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\AbstractType;
use CanalTP\MttBundle\Twig\CalendarExtension;

class FrequencyType extends AbstractType
{
    private $startHours;
    private $endHours;

    public function __construct($hoursRange)
    {
        $this->startHours = $hoursRange;
        $this->endHours = $hoursRange;
        array_shift($this->endHours);
    }
    
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('startTime', 'time', array(
            'with_minutes'  => false,
            'hours' => $this->startHours
        ));
        $builder->add('endTime', 'time', array(
            'with_minutes'  => false,
            'hours' => $this->endHours
        ));
        $builder->add('content', 'textarea', array(
            'attr' => array(
                'maxlength' => 150,
            )
        ));
    }
    
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class'    => 'CanalTP\MttBundle\Entity\Frequency'
        ));
    }
    
    public function getName()
    {
        return 'frequency';
    }
}