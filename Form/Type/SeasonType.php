<?php

namespace CanalTP\MttBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use CanalTP\MttBundle\Form\EventListener\SeasonLockedSubscriber;

class SeasonType extends AbstractType
{
    private $seasons = null;
    private $currentSeasonId = null;

    public function __construct($seasons, $seasonId)
    {
        $this->seasons = array();
        $this->currentSeasonId = $seasonId;

        $this->fetchSeasons($seasons);
    }

    private function fetchSeasons($seasons)
    {
        foreach ($seasons as $season) {
            $this->seasons[$season->getId()] = $season->getTitle();
        }
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'title',
            'text',
            array(
                'label' => 'season.labels.title'
                )
        );
        $builder->add(
            'startDate',
            'datepicker',
            array(
                'attr' => array(
                    'data-from-date' => true
                ),
                'label' => 'season.labels.start_date'
            )
        );
        $builder->add(
            'endDate',
            'datepicker',
            array(
                'attr' => array(
                    'data-to-date' => true
                ),
                'label' => 'season.labels.end_date'
            )
        );
        if (count($this->seasons) > 0 && !$this->currentSeasonId) {
            $builder->add(
                'seasonToClone',
                'choice',
                array(
                    'choices' => $this->seasons,
                    'empty_value' => 'global.please_choose',
                    'required' => false,
                    'label' => 'season.labels.season_to_clone'
                )
            );
        }
        $builder->setAction($options['action']);
        $builder->addEventSubscriber(new SeasonLockedSubscriber());
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class' => 'CanalTP\MttBundle\Entity\Season'
            )
        );
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'mtt_season';
    }
}
