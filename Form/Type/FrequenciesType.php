<?php
namespace CanalTP\MttBundle\Form\Type;

use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\AbstractType;
use Doctrine\Common\Collections\ArrayCollection;

use CanalTP\MttBundle\Twig\CalendarExtension;
use CanalTP\MttBundle\Entity\Block;
use CanalTP\MttBundle\Entity\Frequency;
use CanalTP\MttBundle\Validator\Constraints\NotOverlapping;

/*
 * CalendarController
 */
class FrequenciesType extends AbstractType
{
    private $hoursRange;

    public function __construct(Block $block)
    {
        $layout = $block->getTimetable()->getLineConfig()->getLayoutConfig();
        $extension = new CalendarExtension();
        $this->hoursRange = $extension->calendarRange($layout);
        // add at least one empty frequency to show empty form
        if (count($block->getFrequencies()) == 0) {
            $frequency = new Frequency();
            $block->setFrequencies(new ArrayCollection(array($frequency)));
        }
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'frequencies',
            'collection',
            array(
                'type'          => new FrequencyType($this->hoursRange),
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
