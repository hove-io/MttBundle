<?php

namespace CanalTP\MttBundle\Form\Type;

use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\AbstractType;

use CanalTP\MttBundle\Twig\CalendarExtension;
use CanalTP\MttBundle\Entity\Block;
use CanalTP\MttBundle\Entity\Frequency;
use CanalTP\MttBundle\Entity\Timetable;
use CanalTP\MttBundle\Entity\StopTimetable;
use CanalTP\MttBundle\Entity\LineTimetable;
use CanalTP\MttBundle\Validator\Constraints\NotOverlapping;

/*
 * CalendarController
 */
class FrequenciesType extends AbstractType
{
    private $hoursRange;
    private $type;

    public function __construct(Block $block)
    {
        $layout = $block->getTimetable()->getLineConfig()->getLayoutConfig();
        $extension = new CalendarExtension();
        $this->hoursRange = $extension->calendarRange($layout);

        // add at least one empty frequency to show empty form
        if ($block->getFrequencies()->isEmpty()) {
            $block->addFrequency(new Frequency());
        }

        // specify the type of timetable linked to the block
        if ($block->getTimetable() instanceof StopTimetable) {
            $this->type = Timetable::STOP_TYPE;
        } else if ($block->getTimetable() instanceof LineTimetable) {
            $this->type = Timetable::LINE_TYPE;
        } else {
            throw new \Exception('The block is not linked to a correct Timetable object');
        }
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'frequencies',
            'collection',
            array(
                'type'          => new FrequencyType($this->hoursRange, $this->type),
                'allow_add'     => true,
                'allow_delete'  => true,
                'by_reference'  => false,
                'constraints'   => array(
                    new NotOverlapping(
                        array(
                            'values' => $this->hoursRange,
                            'startField'=>'startTime',
                            'endField'=>'endTime'
                        )
                    )
                )
            )
        );
        $builder->setAction($options['action']);
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class'    => 'CanalTP\MttBundle\Entity\Block',
                'attr'          => array(
                    'class' => 'form-with-collection',
                )
            )
        );
    }

    public function getName()
    {
        return 'block_frequencies_coll';
    }
}
