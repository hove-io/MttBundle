<?php

namespace CanalTP\MttBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use CanalTP\MttBundle\Entity\Template;

class LineTimetableController extends AbstractController
{
    /**
     * Listing all available lines and displaying a LineTimetable configuration for selected one
     * If no externalLineId is provided, selecting first line found in Navitia by default
     *
     * @param $externalNetworkId
     * @param mixed $externalLineId
     * @param mixed $seasonId
     */
    public function listAction($externalNetworkId, $externalLineId = null, $seasonId = null)
    {
        $this->isGranted('BUSINESS_MANAGE_LINE_TIMETABLE');

        $navitia = $this->get('canal_tp_mtt.navitia');
        $customer = $this->getUser()->getCustomer();

        $perimeter = $this->get('nmm.perimeter_manager')->findOneByExternalNetworkId(
            $customer,
            $externalNetworkId
        );

        $seasons = $this->get('canal_tp_mtt.season_manager')->findByPerimeter($perimeter);
        $currentSeason = $this->get('canal_tp_mtt.season_manager')->getSelected($seasonId, $seasons);
        $this->addFlashIfSeasonLocked($currentSeason);

        // No externalLineId provided, get first one found
        if (empty($externalLineId))
        {
            $externalLineId = $navitia->getFirstLineOfNetwork(
                $perimeter->getExternalCoverageId(),
                $externalNetworkId
            );
        }

        $externalLineData = $navitia->getLine(
            $perimeter->getExternalCoverageId(),
            $externalNetworkId,
            $externalLineId
        );

        $lineConfig = $this->getDoctrine()
            ->getRepository('CanalTPMttBundle:LineConfig')
            ->findOneBy(
                array(
                    'externalLineId'    => $externalLineId,
                    'season'            => $currentSeason
                )
            );

        if (!empty($lineConfig))
        {
            $lineTimetable = $this->get('canal_tp_mtt.line_timetable_manager')
                ->findOrCreateLineTimetable($lineConfig);
        }

        return $this->render(
            'CanalTPMttBundle:LineTimetable:list.html.twig',
            array(
                'externalNetworkId' => $externalNetworkId,
                'currentSeason'     => $currentSeason,
                'seasons'           => $seasons,
                'externalLineData'  => $externalLineData,
                'externalLineId'    => $externalLineData->id,
                'lineTimetable'     => isset($lineTimetable) ? $lineTimetable : null
            )
        );
    }
}
