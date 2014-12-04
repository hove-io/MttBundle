<?php
namespace CanalTP\MttBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\FormInterface;

class LayoutsCustomerType extends AbstractType
{

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'multiple'=> true,
                'layoutConfigs' => array(),
                'class' => 'CanalTP\MttBundle\Entity\Layout'
            )
        );
    }

     /**
     * Passe la config du champ à la vue
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $layouts = array();

        foreach ($options['layoutConfigs'] as $layout) {
            $layouts[$layout->getId()] = $layout;
        }
        $view->vars['layoutConfigs'] = $layouts;
    }

    public function getParent()
    {
        return 'layout_config_customer';
    }

    public function getName()
    {
        return 'layouts_customer';
    }
}
