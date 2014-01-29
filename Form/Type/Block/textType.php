<?php
namespace CanalTP\MethBundle\Form\Type\Block;

use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\AbstractType;

class textType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', 'text')
            ->add('content', 'textarea', array('attr' => array('rows' => 5)))
            ->add('dom_id', 'hidden', array('data' => $options['data']['dom_id']))
            ->add('type_id', 'hidden', array('data' => $options['data']['type_id']))
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'CanalTP\MethBundle\Entity\Block',
        ));
    }

    public function getName()
    {
        return 'text_block';
    }
}
