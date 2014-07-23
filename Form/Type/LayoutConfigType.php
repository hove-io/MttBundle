<?php
namespace CanalTP\MttBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\FormInterface;

class LayoutConfigType extends AbstractType
{

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'multiple'=> false,
                'layoutConfigs' => array(),
                'class' => 'CanalTP\MttBundle\Entity\LayoutConfig',
            )
        );
    }

    /**
     * Passe la config du champ à la vue
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $layoutConfigs = array();

        foreach ($options['layoutConfigs'] as $layoutConfig) {
            $layoutConfigs[$layoutConfig->getId()] = $layoutConfig;
        }
        $view->vars['layoutConfigs'] = $layoutConfigs;
    }

    public function getParent()
    {
        return 'entity';
    }

    public function getName()
    {
        return 'layout_config';
    }
}
