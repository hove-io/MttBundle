<?php
namespace CanalTP\MethBundle\Form\Type\Block;

use Symfony\Component\Form\FormBuilderInterface;
use CanalTP\MethBundle\Form\Type\BlockType;

class ImgType extends BlockType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', 'text')
            ->add('content', 'file', array('data_class' => null))
        ;
        parent::buildForm($builder, $options);
    }

    public function getName()
    {
        return 'img_block';
    }
}
