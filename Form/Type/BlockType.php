<?php
namespace CanalTP\MttBundle\Form\Type;

use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\AbstractType;

use CanalTP\MttBundle\Form\EventListener\SeasonLockedSubscriber;

class BlockType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('domId', 'hidden', array('data' => $options['data']['domId']));
        $builder->add('type', 'hidden', array('data' => $options['data']['type']));

        $builder->addEventSubscriber(new SeasonLockedSubscriber());
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class' => 'CanalTP\MttBundle\Entity\Block'
            )
        );
    }

    public function getName()
    {
        return 'generic_block';
    }
}
