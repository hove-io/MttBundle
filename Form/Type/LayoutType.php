<?php
namespace CanalTP\MethBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\FormInterface;

class LayoutType extends AbstractType
{
    private $layoutChoices;

    private $layouts;

    public function __construct($layoutChoices)
    {
        $this->layouts = $layoutChoices;
        foreach ($layoutChoices as $db_value => $choice) {
            $this->layoutChoices[$db_value] = $choice['label'];
        }
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'multiple'=> false,
            'choices' => $this->layoutChoices
        ));
    }

    /**
     * Passe la config du champ à la vue
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['layouts'] = $this->layouts;
    }

    public function getParent()
    {
        return 'choice';
    }

    public function getName()
    {
        return 'layout';
    }
}
