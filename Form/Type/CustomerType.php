<?php

namespace CanalTP\MttBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;
use CanalTP\MttBundle\Form\DataTransformer\LayoutConfigCustomerTransformer;

class CustomerType extends AbstractType
{
    private $layoutConfigs = null;
    private $customerId = null;

    public function __construct($layoutConfigs, $customerId)
    {
        $this->layoutConfigs = $layoutConfigs;
        $this->customerId = $customerId;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'layoutConfigsAssigned',
            'layout_configs_network',
            array(
                'label' => 'customer.layout_list',
                'choices' => $this->layoutConfigs,
                'layoutConfigs' => $this->layoutConfigs,
                'empty_value' => 'global.please_choose'
            )
        )->addModelTransformer(new LayoutConfigCustomerTransformer(
            $options['em'], $this->customerId
        ));

        $builder->setAction($options['action']);
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class' => 'CanalTP\MttBundle\Entity\LayoutConfigCustomer'
            )
        )
        ->setRequired(array('em'))
        ->setAllowedTypes(array(
                'em' => 'Doctrine\Common\Persistence\ObjectManager',
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
