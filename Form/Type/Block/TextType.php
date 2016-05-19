<?php
namespace CanalTP\MttBundle\Form\Type\Block;

use Symfony\Component\Form\FormBuilderInterface;
use CanalTP\MttBundle\Form\Type\BlockType;

class TextType extends BlockType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', 'text', array(
                    'label' => 'block.text.labels.title',
                    'required' => false
                ))
            ->add('content', 'textarea', array(
                    'attr' => array('rows' => 5),
                    'label' => 'block.text.labels.content',
                    'required' => false
                ))
        ;
        parent::buildForm($builder, $options);
    }

    public function getName()
    {
        return 'text_block';
    }
}
