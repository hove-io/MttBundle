<?php
namespace CanalTP\MttBundle\Form\Type\Block;

use Symfony\Component\Form\FormBuilderInterface;
use CanalTP\MttBundle\Form\Type\BlockType;

class PageBreakType extends BlockType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
                'title', 'hidden',
                    array(
                        'data' => 'Page Break',
                        'disabled' => true,
                        'label' => 'block.calendar.labels.page_break',
                        'required' => false
                    )
        )
        ->add(
            'content',
            'choice',
            array(
                'choices'       => array(0 => 'block.calendar.labels.no', 1 => 'block.calendar.labels.yes'),
                'label' => 'block.calendar.labels.page_break'
            )
        );
        parent::buildForm($builder, $options);
    }

    public function getName()
    {
        return 'pageBreaker_block';
    }
}
