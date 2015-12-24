<?php

namespace CanalTP\MttBundle\Form\Type;

use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\DateTime;
use CanalTP\MttBundle\Entity\Timetable;

class FrequencyType extends AbstractType
{
    private $hoursRange;
    private $endHours;
    private $type;

    public function __construct($hoursRange, $type)
    {
        $this->hoursRange = $hoursRange;
        $this->type = $type;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if ($this->type == Timetable::LINE_TYPE) {
            $builder
                ->add(
                    'startTime',
                    'time',
                    array(
                        'with_minutes'  => true,
                        'hours' => $this->hoursRange,
                        'minutes' => array('00', '30'),
                        'constraints' => array(
                            new NotBlank(),
                            new DateTime()
                        ),
                        'label' => 'frequency.labels.start_time'
                    )
                )
                ->add(
                    'endTime',
                    'time',
                    array(
                        'with_minutes'  => true,
                        'hours' => $this->hoursRange,
                        'minutes' => array('59', '29'),
                        'constraints' => array(
                            new NotBlank(),
                            new DateTime()
                        ),
                        'label' => 'frequency.labels.end_time'
                    )
                )
                ->add(
                    'columns',
                    'text',
                    array('label' => 'frequency.labels.columns')
                )
                ->add(
                    'time',
                    'number',
                    array('label' => 'frequency.labels.time')
                )
                ->add(
                    'check',
                    'button',
                    array(
                        'label' => 'frequency.action.check'
                    )
                )
            ;
        } else if ($this->type == Timetable::STOP_TYPE) {
            $builder
                ->add(
                    'startTime',
                    'time',
                    array(
                        'with_minutes'  => false,
                        'hours' => $this->hoursRange,
                        'constraints' => array(
                            new NotBlank(),
                            new DateTime()
                        ),
                        'label' => 'frequency.labels.start_time'
                    )
                )
                ->add(
                    'endTime',
                    'time',
                    array(
                        'with_minutes'  => false,
                        'hours' => $this->endHours,
                        'constraints' => array(
                            new NotBlank(),
                            new DateTime()
                        ),
                        'label' => 'frequency.labels.end_time'
                    )
                )
            ;
        }

        $builder->add(
            'content',
            'textarea',
            array(
                'attr' => array(
                    'maxlength' => 150
                ),
                'constraints' => array(
                    new NotBlank(),
                    new Length(
                        array('max' => 150)
                    )
                ),
                'label' => 'frequency.labels.content'
            )
        );
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class'    => 'CanalTP\MttBundle\Entity\Frequency'
            )
        );
    }

    public function getName()
    {
        return 'frequency';
    }
}
