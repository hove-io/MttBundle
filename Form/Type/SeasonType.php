<?php

namespace CanalTP\MttBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\FormInterface;
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
    
    /* public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $startDate = $form->getData()->getStartDate();
        if (!empty($startDate)){
            $children = $form->all();
            print_r($children['endDate']->getConfig()->getAttributes());
            // (array('attr'=> array('data-start-date' => (string)$startDate))));
            die;
        }
    } */

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
