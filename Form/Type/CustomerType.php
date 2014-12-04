<?php

namespace CanalTP\MttBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use CanalTP\MttBundle\Form\DataTransformer\LayoutCustomerTransformer;

class CustomerType extends AbstractType
{
    private $layouts = null;
    private $customerId = null;

    public function __construct($layouts, $customerId)
    {
        $this->layouts = $layouts;
        $this->customerId = $customerId;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'layoutsAssigned',
            'layouts_customer',
            array(
                'label' => 'customer.layout_list',
                'choices' => $this->layouts,
                'layoutConfigs' => $this->layouts,
                'empty_value' => 'global.please_choose'
            )
        )->addModelTransformer(new LayoutCustomerTransformer(
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
                'data_class' => 'CanalTP\MttBundle\Entity\LayoutCustomer'
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
