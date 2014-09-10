<?php
namespace CanalTP\MttBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\FormInterface;

class LayoutType extends AbstractType
{

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'multiple'=> false,
                'layouts' => array(),
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

        foreach ($options['layouts'] as $layout) {
            $layouts[$layout->getId()] = $layout;
        }
        $view->vars['layouts'] = $layouts;
    }

    public function getParent()
    {
        return 'entity';
    }

    public function getName()
    {
        return 'layout';
    }
}
