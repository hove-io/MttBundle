<?php
namespace CanalTP\MttBundle\Form\Type\Block;

use Symfony\Component\Form\FormBuilderInterface;
use CanalTP\MttBundle\Form\Type\BlockType;

class PageBreakType extends BlockType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
                'title', 'text',
                    array(
                        'data' => 'Line Break',
                        'disabled' => true,
                        'label' => 'block.pageBreaker.labels.pageBreak',
                        'required' => false
                    )
        )
        ->add(
            'content',
            'choice',
            array(
                'choices'       => array(0 => 'non', 1 => 'oui'),
                'label' => 'block.calendar.labels.color'
            )
        );
        parent::buildForm($builder, $options);
    }

    public function getName()
    {
        return 'pageBreaker_block';
    }
}
