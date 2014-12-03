<?php

namespace CanalTP\MttBundle\Twig;

use CanalTP\MttBundle\Entity\AmqpTask;

class TaskTypeExtension extends \Twig_Extension
{
    private $translator;
    private $em;
    private $distributionListManager;
    private $areaPdfManager;
    private $navitiaManager;

    public function __construct(
        $translator,
        $em,
        $distributionListManager,
        $areaPdfManager,
        $navitiaManager
        )
    {
        $this->distributionListManager = $distributionListManager;
        $this->areaPdfManager = $areaPdfManager;
        $this->translator = $translator;
        $this->em = $em;
        $this->navitiaManager = $navitiaManager;
    }

    public function getFilters()
    {
        return array(
            'taskType'      => new \Twig_Filter_Method(
                $this,
                'taskType',
                array("is_safe" => array("html"))
            ),
            'taskActions'   => new \Twig_Filter_Method($this, 'taskActions', array("is_safe" => array("html"))),
            'taskStatus'    => new \Twig_Filter_Method($this, 'taskStatus'),
        );
    }

    public function taskActions($task)
    {
        $return = '';
        switch ($task->getTypeId()) {
            case AmqpTask::DISTRIBUTION_LIST_PDF_GENERATION_TYPE:
                if ($task->isCompleted()) {
                    $timetable = $this->em->getRepository('CanalTPMttBundle:Timetable')->find($task->getObjectId());
                    if (!empty($timetable)) {
                        $return = '<a class="btn btn-primary btn-sm" target="_blank" href="' . $this->distributionListManager->findPdfPathByTimetable($timetable) . '">';
                        $return .= '<span class="glyphicon glyphicon-download-alt"></span> ';
                        $return .= $this->translator->trans(
                            'distribution.download_distribution_pdf',
                            array(),
                            'default'
                        );
                        $return .= '</a>';
                    }
                }
                break;
            case AmqpTask::AREA_PDF_GENERATION_TYPE:
            default:
                if ($task->isCompleted()) {
                    $areaPdf = $this->em->getRepository('CanalTPMttBundle:AreaPdf')->find($task->getObjectId());
                    if (!empty($areaPdf)) {
                        $return = '<a class="btn btn-primary btn-sm" target="_blank" href="' . $this->areaPdfManager->findPdfPath($areaPdf) . '">';
                        $return .= '<span class="glyphicon glyphicon-download-alt"></span> ';
                        $return .= $this->translator->trans(
                            'distribution.download_distribution_pdf',
                            array(),
                            'default'
                        );
                        $return .= '</a>';
                    }
                }
                break;
        }

        return $return;
    }

    public function taskType($task)
    {
        $return = '';
        switch ($task->getTypeId()) {
            case AmqpTask::DISTRIBUTION_LIST_PDF_GENERATION_TYPE:
                $timetable = $this->em->getRepository('CanalTPMttBundle:Timetable')->find($task->getObjectId());
                if (!empty($timetable)) {
                    $navitiaResult = $this->navitiaManager->getRoute(
                        $timetable->getLineConfig()->getSeason()->getPerimeter()->getExternalCoverageId(),
                        $timetable->getExternalRouteId()
                    );
                    if (isset($navitiaResult->routes)) {
                        $return = $this->translator->trans(
                            'task.distribution_list_pdf_generation',
                            array(
                                '%routeName%' => $navitiaResult->routes[0]->name
                            ),
                            'default'
                        );
                    }
                }
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
            case AmqpTask::AREA_PDF_GENERATION_TYPE:
            default:
                $areaPdf = $this->em->getRepository('CanalTPMttBundle:AreaPdf')->find($task->getObjectId());
                $return = $this->translator->trans(
                    'task.area_pdf_generation',
                    array(
                        '%sectorLabel%' => $areaPdf->getArea()->getLabel()
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
                $key = 'task.status.launched';
                break;
            case AmqpTask::CANCELED_STATUS:
                $key = 'task.status.canceled';
                break;
            case AmqpTask::ERROR_STATUS:
                $key = 'task.status.failed';
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
