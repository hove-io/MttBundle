<?php

namespace CanalTP\MttBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class NetworkType extends AbstractType
{
    private $coverages = null;
    private $networkExist = null;

    public function __construct($coverages, $networkId)
    {
        $this->coverages = array();
        $this->networkExist = ($networkId != null);

        $this->fetchCoverages($coverages);
    }

    private function fetchCoverages($coverages)
    {
        foreach ($coverages as $coverage) {
            $this->coverages[$coverage->id] = $coverage->id;
        }
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'external_coverage_id',
            'choice',
                array(
                    'choices' => $this->coverages,
                    'empty_value' => 'global.please_choose'
            )
        );
        if ($this->networkExist) {            
            $builder->add(
                'external_id',
                'choice',
                    array(
                        'choices' => array(),
                        'empty_value' => 'global.please_choose'
                )
            );
        } else {
            $builder->add('external_id', 'hidden');
        }
        $builder->setAction($options['action']);
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'CanalTP\MttBundle\Entity\Network'
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
