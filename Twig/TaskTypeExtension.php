<?php

namespace CanalTP\MttBundle\Twig;

use CanalTP\MttBundle\Entity\AmqpTask;

class TaskTypeExtension extends \Twig_Extension
{
    public function getFilters()
    {
        return array(
            'taskType' => new \Twig_Filter_Method($this, 'taskType'),
            'taskStatus' => new \Twig_Filter_Method($this, 'taskStatus'),
        );
    }
    
    public function taskType($taskTypeId)
    {
        switch ($taskTypeId) {
            case AmqpTask::SEASON_PDF_GENERATION_TYPE:
            default:
                $key = 'task.season_pdf_generation';
                break;
        }
        return $key;
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
