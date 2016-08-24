<?php

namespace CanalTP\MttBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class CalendarType extends AbstractType implements DataTransformerInterface
{

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', 'text')
            ->add('startDate', 'datepicker')
            ->add('endDate', 'datepicker')
            ->add(
                'weeklyPattern',
                'choice',
                [
                    'choices' => [
                        'calendar.weekdays.monday',
                        'calendar.weekdays.tuesday',
                        'calendar.weekdays.wednesday',
                        'calendar.weekdays.thursday',
                        'calendar.weekdays.friday',
                        'calendar.weekdays.saturday',
                        'calendar.weekdays.sunday'
                    ],
                    'multiple' => true,
                    'expanded' => true
                ]
            )
            ->add('save', 'submit')
        ;

        $builder->get('weeklyPattern')->addModelTransformer($this);
    }

    /**
     * Transform a weeklyPattern to checkbox values
     * Ex: $weeklyPattern = "1010000"
     * return [
     *     0 => 0,
     *     1 => 2
     * ]
     *
     * The first and third checkbox will be selected
     *
     * @param string $weeklyPattern
     *
     * @return array
     */
    public function transform($weeklyPattern)
    {
        if (null === $weeklyPattern) {
            return null;
        }

        $selectedDays = array_filter(str_split($weeklyPattern), function ($value) {
            return $value == 1;
        });

        return array_keys($selectedDays);
    }

    /**
     * Transform an array of checkbox values to a weeklyPattern
     * Ex:
     * $selectedValues = [
     *     0 => 0,
     *     1 => 2
     * ]
     * return "1010000"
     *
     * @param array $selectedDays
     *
     * @return string
     */
    public function reverseTransform($selectedDays)
    {
        if (null === $selectedDays) {
            return null;
        }

        $alldDays = array_fill(0, 7, 0);
        $selectedDays = array_fill_keys($selectedDays, 1);

        return implode(array_replace($alldDays, $selectedDays));
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
