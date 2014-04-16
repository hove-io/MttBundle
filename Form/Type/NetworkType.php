<?php

namespace CanalTP\MttBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;

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
     * @param array                $options
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
        $builder->add(
            'external_id',
            'choice',
            array(
                'choices' => array(),
                'empty_value' => 'global.please_choose'
            )
        );

        $formFactory = $builder->getFormFactory();
        $callback = function (FormEvent $event) use ($formFactory) {
            $data = $event->getData();
            $form = $event->getForm();
            $form->remove('external_id');

            $form->add(
                $formFactory->createNamed(
                    'external_id',
                    'choice',
                    null,
                    array(
                        'auto_initialize' => false,
                        'choices' => array($data['external_id'] => $data['external_id'])
                    )
                )
            );
        };

        $builder->addEventListener(FormEvents::PRE_SET_DATA, $callback);
        $builder->addEventListener(FormEvents::PRE_BIND, $callback);

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
        return 'mtt_network';
    }
}
