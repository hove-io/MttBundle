<?php

namespace CanalTP\MttBundle\Form\Type\Block;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use CanalTP\MttBundle\Form\EventListener\SeasonLockedSubscriber;
use CanalTP\MttBundle\Form\DataTransformer\EntityToIntTransformer;
use CanalTP\MttBundle\Entity\BlockRepository;

class BlockType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $timetable = $builder->getData()->getTimetable();

        if (empty($timetable)) {
            throw new \Exception('The block should have a timetable object linked to it');
        }

        $entityTransformer = new EntityToIntTransformer(
            $options["em"],
            get_class($timetable)
        );

        $builder->add(
            $builder->create(
                'timetable',
                'hidden'
            )->addModelTransformer($entityTransformer)
        );

        $builder
            ->add(
                'type',
                'choice',
                array(
                    'label' => 'block.labels.type',
                    'choices' => array(
                        BlockRepository::CALENDAR_TYPE => 'block.'.BlockRepository::CALENDAR_TYPE.'.labels.content',
                        BlockRepository::TEXT_TYPE => 'block.'.BlockRepository::TEXT_TYPE.'.labels.content'
                    ),
                    'required' => true
                )
            )
        ;
        $builder->add(
            'rank',
            'hidden'
        );
        $builder->add(
            'number',
            'choice',
            array(
                'label' => 'block.labels.number',
                'mapped' => false,
                'choices' => array(1 => 1, 2 => 2, 3 => 3, 4 => 4, 5 => 5)
            )
        );
        $builder->add(
            'externalLineId',
            'hidden'
        );

        $builder->addEventSubscriber(new SeasonLockedSubscriber());
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver
            ->setDefaults(array(
                'data_class' => 'CanalTP\MttBundle\Entity\Block'
            ))
            ->setRequired(array(
                'em',
            ))
            ->setAllowedTypes(array(
                'em' => 'Doctrine\Common\Persistence\ObjectManager',
            ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'mtt_block';
    }
}
