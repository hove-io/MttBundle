<?php

namespace CanalTP\MttBundle\Twig;

use CanalTP\MttBundle\Entity\AmqpTask;

class TaskTypeExtension extends \Twig_Extension
{
    private $translator;
    public function __construct($translator, $em)
    {
        $this->translator = $translator;
        $this->em = $em;
    }

    public function getFilters()
    {
        return array(
            'taskType' => new \Twig_Filter_Method($this, 'taskType'),
            'taskStatus' => new \Twig_Filter_Method($this, 'taskStatus'),
        );
    }
    
    public function taskType($task)
    {
        switch ($task->getTypeId()) {
            case AmqpTask::DISTRIBUTION_LIST_PDF_GENERATION_TYPE:
                $timetable = $this->em->getRepository('CanalTPMttBundle:Timetable')->find($task->getObjectId());
                $return = $this->translator->trans(
                    'task.distribution_list_pdf_generation',
                    array(
                        '%routeId%' => $timetable->getExternalRouteId()
                    ),
                    'default'
                );
                break;
            case AmqpTask::SEASON_PDF_GENERATION_TYPE:
            default:
                $season = $this->em->getRepository('CanalTPMttBundle:Season')->find($task->getObjectId());
                $return = $this->translator->trans(
                    'task.season_pdf_generation',
                    array(
                        '%seasonName%' => $season->getTitle()
                    ),
                    'default'
                );
                break;
        }
        return $return;
    }
    
    public function taskStatus($taskStatus)
    {
        switch ($taskStatus) {
            case AmqpTask::LAUNCHED_STATUS:
            default:
                $key = 'task.status.launched';
                break;
            case AmqpTask::CANCELED_STATUS:
                $key = 'task.status.canceled';
                break;
            case AmqpTask::COMPLETED_STATUS:
                $key = 'task.status.completed';
                break;
        }
        return $key;
    }


    public function getName()
    {
        return 'task_type_extension';
    }
}
