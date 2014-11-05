<?php
namespace CanalTP\MttBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

use CanalTP\MttBundle\Form\EventListener\SeasonLockedSubscriber;

class LineConfigType extends AbstractType
{
    private $layoutConfigs;

    public function __construct($layoutConfigs)
    {
        $this->layoutConfigs = $layoutConfigs;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'layout_config',
            'layout_config_customer',
            array(
                'choices' => $this->layoutConfigs,
                'layoutConfigs' => $this->layoutConfigs,
                'empty_value' => 'global.please_choose',
                'constraints' => array(
                    new NotBlank()
                )
            )
        );
        // $builder->addEventSubscriber(new SeasonLockedSubscriber());
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class' => 'CanalTP\MttBundle\Entity\LineConfig'
            )
        );
    }

    public function getName()
    {
        return 'line_config';
    }
}
