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
        $builder->add('dom_id', 'hidden', array('data' => $options['data']['dom_id']));
        $builder->add('type_id', 'hidden', array('data' => $options['data']['type_id']));

        if (isset($options['data']['stop_point']) && $options['data']['stop_point'] != null) {
            $builder->add('stop_point', 'hidden', array('data' => $options['data']['stop_point']));
        }
        $builder->addEventSubscriber(new SeasonLockedSubscriber());
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class'         => 'CanalTP\MttBundle\Entity\Block'
            )
        );
    }

    public function getName()
    {
        return 'generic_block';
    }
}
