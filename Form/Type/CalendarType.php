<?php

namespace CanalTP\MttBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use CanalTP\MttBundle\Form\DataTransformer\LayoutCustomerTransformer;

class CalendarType extends AbstractType
{

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', 'text', ['label' => 'calendar.form.title'])
            ->add(
                'startDate',
                'datepicker',
                [
                    'label' => 'calendar.form.start_date',
                    'attr' => [
                        'class' => 'datepicker'
                    ]
                ]
            )
            ->add(
                'endDate',
                'datepicker',
                [
                    'label' => 'calendar.form.end_date',
                    'attr' => [
                        'class' => 'datepicker'
                    ]
                ]
            )
            ->add(
                'weeklyPattern',
                'text',
                [
                    'label' => 'calendar.form.weekly_pattern',
                    'attr' => [
                        'maxlength' => 7,
                    ]
                ]
            );
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class' => 'CanalTP\MttBundle\Entity\Calendar',
                'translation_domain' => 'default',
                'error_mapping' => [
                    'datesValid' => 'endDate'
                ]
            )
        );
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'mtt_calendar';
    }
}
