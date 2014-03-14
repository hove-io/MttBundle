<?php

namespace CanalTP\MttBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class SeasonType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('title', 'text');
        $builder->add('startDate', 'datepicker', array(
            'attr' => array(
                'data-from-date' => true
            )
        ));
        $builder->add('endDate', 'datepicker', array(
            'attr' => array(
                'data-to-date' => true
            )
        ));
        $builder->setAction($options['action']);
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'CanalTP\MttBundle\Entity\Season'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'mtt_season';
    }
}
