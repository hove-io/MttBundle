<?php
namespace CanalTP\MethBundle\Form\Type\Block;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormError;

use CanalTP\MethBundle\Form\Type\BlockType;

class CalendarType extends BlockType
{
    private $calendarManager = null;
    private $externalCoverageId = null;
    private $blockInstance = null;
    private $choices = null;

    public function __construct($calendarManager, $instance, $externalCoverageId)
    {
        $this->calendarManager = $calendarManager;
        $this->blockInstance = $instance;
        $this->externalCoverageId = $externalCoverageId;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $calendars = $this->calendarManager->getCalendarsForRoute(
            $this->externalCoverageId,
            $this->blockInstance->getTimetable()->getExternalRouteId()
        );
        $this->choices = $this->getChoices($calendars);

        $builder
            ->add(
                'title',
                'text'
            )
            ->add(
                'content',
                'choice',
                array(
                    'choices'       => $this->choices,
                    'disabled'      => count($this->choices) == 1 && $this->blockInstance->getContent() != NULL,
                    'label'         => 'calendar.form.label',
                    'attr'      => array(
                        // attribute to tell javascript to fill automatically title
                        // when a change occurs on this field
                        'data-fill-title'   => true,
                    )
                )
            );

        parent::buildForm($builder, $options);
    }

    /*
     * @function filter calendars and remove already used calendars by others in the parent timetable
     */
    private function getChoices($calendars)
    {
        // retrieve other blocks on this timetable
        $blocks = $this->blockInstance->getTimetable()->getBlocks();
        // keep only calendar blocks
        $usedCalendars = array();
        for ($i = 0; $i < count($blocks);$i++) {
            if ($blocks[$i]->getTypeId() == 'calendar') {
                $usedCalendars[] = $blocks[$i]->getContent();
            }
        }
        $choices = array();
        foreach ($calendars as $calendar) {
            foreach ($blocks as $block) {
                if (
                    !in_array($calendar->id, $usedCalendars) ||
                    $calendar->id == $this->blockInstance->getContent()
                    ) {
                    $choices[$calendar->id] = $calendar->name;
                }
            }
        }

        return $choices;
    }

    /**
     * Passe la config du champ à la vue
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        if ($form->get('content')->isDisabled()) {
            $form->addError(new FormError(''));
            $form->get('content')->addError(new FormError('calendar.form.error.all_calendars_selected'));
        }
    }

    public function getName()
    {
        return 'calendar_block';
    }
}
