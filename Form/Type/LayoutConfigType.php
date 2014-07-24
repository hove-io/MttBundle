<?php
namespace CanalTP\MttBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\Range;
use Symfony\Component\Validator\Constraints\File;

class LayoutConfigType extends AbstractType
{
    private $layouts;
    private $hours;

    public function __construct($layouts)
    {
        $this->layouts = $layouts;
        $this->hours = range(0, 23);
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'label',
            'text',
            array(
                'label' => 'layout_config.labels.label',
                'constraints' => array(
                    new NotBlank(),
                    new Length(
                        array('max' => 255)
                    )
                )
            )
        );
        $builder->add(
            'calendarStart',
            'choice',
            array(
                'choices' => $this->hours,
                'constraints' => array(
                    new NotBlank(),
                    new Range(array(
                            'min' => 0,
                            'max' => 23
                        )
                    )
                ),
                'label' => 'layout_config.labels.calendar_start'
            )
        );
        $builder->add(
            'calendarEnd',
            'choice',
            array(
                'choices' => $this->hours,
                'constraints' => array(
                    new NotBlank()
                ),
                'label' => 'layout_config.labels.calendar_end'
            )
        );
        $builder->add(
            'file',
            'file',
            array(
                'label' => 'layout_config.labels.preview_path',
                'required' => false,
                'constraints' => array(
                    new File(array(
                            'maxSize' => '5M'
                        )
                    )
                )
            )
        );
        $builder->add(
            'layout',
            'layout',
            array(
                'choices' => $this->layouts,
                'layouts' => $this->layouts,
                'empty_value' => 'global.please_choose',
                'label' => 'layout_config.labels.layout',
                'constraints' => array(
                    new NotBlank()
                )
            )
        );
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class' => 'CanalTP\MttBundle\Entity\LayoutConfig'
            )
        );
    }

    public function getName()
    {
        return 'layout_config';
    }
}
