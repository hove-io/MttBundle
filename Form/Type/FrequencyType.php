<?php
namespace CanalTP\MttBundle\Form\Type;

use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\AbstractType;
use CanalTP\MttBundle\Twig\CalendarExtension;
use CanalTP\MttBundle\Validator\Constraints\GreaterThanField;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\DateTime;

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
            'hours' => $this->startHours,
            'constraints' => array(
                new NotBlank(),
                new DateTime()
            )
        ));
        $builder->add('endTime', 'time', array(
            'with_minutes'  => false,
            'hours' => $this->endHours,
            'constraints' => array(
                new NotBlank(),
                new DateTime()
            )
        ));
        $builder->add('content', 'textarea', array(
            'attr' => array(
                'maxlength' => 150
            ),
            'constraints' => array(
                new NotBlank(),
                new Length(
                    array('max' => 150)
                )
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