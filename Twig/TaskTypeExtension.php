<?php

namespace CanalTP\MttBundle\Twig;

use Symfony\Bundle\FrameworkBundle\Routing\Router;
use CanalTP\MttBundle\Entity\Area;
use CanalTP\MttBundle\Entity\Season;
use CanalTP\MttBundle\Entity\AmqpTask;

class TaskTypeExtension extends \Twig_Extension
{
    private $router;
    private $translator;
    private $em;
    private $areaPdfManager;
    private $navitiaManager;

    public function __construct(
        Router $router,
        $translator,
        $em,
        $areaPdfManager,
        $navitiaManager
        )
    {
        $this->router = $router;
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
            case AmqpTask::AREA_PDF_GENERATION_TYPE:
                if ($task->isCompleted()) {
                    $areaPdf = $this->em->getRepository('CanalTPMttBundle:AreaPdf')->find($task->getObjectId());
                    if (!empty($areaPdf)) {
                        $return = '<a class="btn btn-primary btn-sm" target="_blank" href="' . $this->areaPdfManager->findPdfPath($areaPdf) . '">';
                        $return .= '<span class="glyphicon glyphicon-download-alt"></span> ';
                        $return .= $this->translator->trans(
                            'area.download_pdf',
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

    private function getSeasonLink(Season $season)
    {
        $seasonManageUrl = $this->router->generate(
            'canal_tp_mtt_season_list',
            array('externalNetworkId' => $season->getPerimeter()->getExternalNetworkId())
        );

        return ('<a href="' . $seasonManageUrl . '">' . $season->getTitle() . '</a>');
    }

    private function getAreaLink(Area $area)
    {
        $areaUrl = $this->router->generate(
            'canal_tp_mtt_area_edit_stops',
            array(
                'externalNetworkId' => $area->getPerimeter()->getExternalNetworkId(),
                'areaId' => $area->getId()
            )
        );

        return ('<a href="' . $areaUrl . '">' . $area->getLabel() . '</a>');
    }

    public function taskType($task)
    {
        $return = '';
        switch ($task->getTypeId()) {
            case AmqpTask::SEASON_PDF_GENERATION_TYPE:
            default:
                $seasonLink = $this->getSeasonLink($this->em->getRepository('CanalTPMttBundle:Season')->find($task->getObjectId()));
                $return = $this->translator->trans(
                    'task.season_pdf_generation',
                    array(
                        '%seasonName%' => $seasonLink
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
                        '%sectorLabel%' => $this->getAreaLink($areaPdf->getArea()),
                        '%seasonTitle%' => $this->getSeasonLink($areaPdf->getSeason())
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
