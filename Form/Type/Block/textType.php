<?php
namespace CanalTP\MethBundle\Form\Type\Block;

use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\AbstractType;

class TextType extends AbstractType
{
    public function __construct()
    {
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', 'text')
            ->add('content', 'textarea', array('attr' => array('rows' => 5)))
            ->add('dom_id', 'hidden', array('data' => $options['data']['dom_id']))
            ->add('type_id', 'hidden', array('data' => $options['data']['type_id']))
        ;
    }

    public function getName()
    {
        return 'text_block';
    }
}
