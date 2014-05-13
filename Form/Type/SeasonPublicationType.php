<?php

namespace CanalTP\MttBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class SeasonPublicationType extends AbstractType
{
    private $seasonId;
    
    public function __construct($seasonId)
    {
        $this->seasonId = $seasonId;
    }
    
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->setAction($options['action']);
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'mtt_season_publication';
    }
}
