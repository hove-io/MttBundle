<?php

namespace CanalTP\MttBundle\Twig;

use CanalTP\MttBundle\Entity\AmqpTask;

class TaskTypeExtension extends \Twig_Extension
{
    public function getFilters()
    {
        return array(
            'taskType' => new \Twig_Filter_Method($this, 'taskType'),
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


    public function getName()
    {
        return 'task_type_extension';
    }
}
