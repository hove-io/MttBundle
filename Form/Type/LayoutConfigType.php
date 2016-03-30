<?php
namespace CanalTP\MttBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\Range;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;

use CanalTP\MttBundle\Entity\LayoutConfig;

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
                        ))
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
            'notesType',
            'choice',
            array(
                'choices' => array(
                    LayoutConfig::NOTES_TYPE_EXPONENT => 'layout_config.labels.exponents',
                    LayoutConfig::NOTES_TYPE_COLOR => 'layout_config.labels.colors'
                ),
                'label' => 'layout_config.labels.notes_type'
            )
        );
        $builder->add(
            'notesColors',
            'collection',
            array(
                'allow_add' => true,
                'type'   => 'text',
                'options'  => array(
                    'required'  => false,
                    'attr'      => array('class' => 'notes-color'),
                ),
                'label' => 'layout_config.labels.notes_colors'
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
                        ))
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

        $builder->addEventListener(FormEvents::POST_SET_DATA, function (FormEvent $event) {
            if (is_null($event->getData())) {
                $form = $event->getForm();

                $form->get('notesColors')->setData(
                    array(
                        '#e44155',
                        '#ff794e',
                        '#4460c5',
                        '#0cc2dd',
                        '#6ebf52',
                        '#bacd40'
                    )
                );
            }
        });
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
