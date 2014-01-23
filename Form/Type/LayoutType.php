<?php 
namespace CanalTP\MethBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\FormInterface;

class LayoutType extends AbstractType
{
    private $layoutChoices;
    
    private $config;
    
    public function __construct($layoutChoices)
    {
        $this->config = $layoutChoices;
        foreach ($layoutChoices as $db_value => $choice)
        {
            $this->layoutChoices[$db_value] = $choice['label'];
        }
    }
    
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        // var_dump($this->layoutChoices);die;
        $resolver->setDefaults(array(
            'multiple'=> false,
            'choices' => $this->layoutChoices
            // 'choices' => array(
                // 'test1'=>'test',
                // 'test2'=>'test2'
            // )
        ));
    }
    
    /**
     * Passe la config du champ à la vue
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['fields_config'] = $this->config;
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