<?php
namespace CanalTP\MttBundle\Form\Type\Block;

use CanalTP\MttBundle\Entity\LineTimecard;
use CanalTP\MttBundle\Entity\Timetable;
use MyProject\Proxies\__CG__\stdClass;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Validator\Constraints\NotBlank;

use CanalTP\MttBundle\Form\Type\BlockType;

class CalendarType extends BlockType
{
    private $calendarManager = null;
    private $externalCoverageId = null;
    private $blockInstance = null;
    private $choices = null;
    private $routeList = null;

    public function __construct($calendarManager, $instance, $externalCoverageId)
    {
        $this->calendarManager = $calendarManager;
        $this->blockInstance = $instance;
        $this->externalCoverageId = $externalCoverageId;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if ($this->blockInstance->getTimetable() instanceof Timetable) {
            $season = $this->blockInstance->getTimetable()->getLineConfig()->getSeason();
            $externalRouteId = $this->blockInstance->getTimetable()->getExternalRouteId();

            $calendars = $this->calendarManager->getCalendarsForRoute(
                $this->externalCoverageId,
                $externalRouteId,
                $season->getStartDate(),
                $season->getEndDate()
            );
        } else if($this->blockInstance->getLineTimecard() instanceof LineTimecard) {
            $calendars = $this->calendarManager->getCalendarsForLine(
                $this->externalCoverageId,
                $this->blockInstance->getLineTimecard()->getLineConfig()->getExternalLineId()
            );
            foreach($this->blockInstance->getLineTimecard()->getTimecards() as $timecard) {
                $item = (object) array(
                    'id' => $timecard->getRouteId(),
                    'name' => $timecard->getRouteId()
                );
                $routes[] = $item;
            }
            unset($item);
            $this->routeList = $this->getChoices($routes);;
        }

        $this->choices = $this->getChoices($calendars);

        $builder
            ->add(
                'title',
                'text',
                array('label' => 'block.calendar.labels.title',)
            )
            ->add(
                'content',
                'choice',
                array(
                    'choices'       => $this->choices,
                    'disabled'      => $this->isDisabled($this->choices),
                    'label'         => 'block.calendar.labels.content',
                    'attr'      => array(
                        // attribute to tell javascript to fill automatically title
                        // when a change occurs on this field
                        'data-fill-title'   => true,
                    ),
                    'constraints' => array(
                        new NotBlank()
                    )
                )
            );

        if ($this->blockInstance->getLineTimecard() instanceof LineTimecard) {
            $builder
                ->add(
                    'color',
                    'text',
                    array(
                        'label' => 'block.calendar.labels.color',
                        'constraints' => array(
                            new NotBlank()
                        )
                    )
                )
                ->add(
                    'route',
                    'choice',
                    array(
                        'choices' => $this->routeList,
                        'disabled'      => $this->isDisabled($this->routeList),
                        'label'         => 'block.calendar.labels.route',
                        'constraints' => array(
                            new NotBlank()
                        )
                    )
                );
        }

        parent::buildForm($builder, $options);
    }

    /**
     * @param array $list list of choices
     * @return bool
     */
    private function isDisabled($list)
    {
        return (count($list) == 1 && $this->blockInstance->getContent() != null);
    }

    private function getChoices($items)
    {
        $choices = array();
        foreach ($items as $item) {
            $choices[$item->id] = $item->name;
        }

        return $choices;
    }

    /**
     * Passe la config du champ à la vue
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        if ($form->get('content')->isDisabled()) {
            $form->get('content')->addError(new FormError('calendar.error.all_calendars_selected'));
        } elseif (count($this->choices) == 0) {
            $form->get('content')->addError(new FormError('calendar.error.no_calendars_found'));
        }
        if ($form->get('route')->isDisabled()) {
            $form->get('content')->addError(new FormError('route.error.all_route_selected'));
        } elseif (count($this->routeList) == 0) {
            $form->get('content')->addError(new FormError('route.error.no_routes_found'));
        }
    }

    public function getName()
    {
        return 'calendar_block';
    }
}
