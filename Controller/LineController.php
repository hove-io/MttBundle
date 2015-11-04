<?php

namespace CanalTP\MttBundle\Controller;

use CanalTP\MttBundle\Entity\LineConfig;
use CanalTP\MttBundle\Entity\Layout;
use CanalTP\MttBundle\Form\Type\LineConfigType;

/*
 * LineController
 */
class LineController extends AbstractController
{
    /*
     * @function process a form to save a layout for a line and a season.
     * Insert a lineConfig element in bdd if needed.
     */
    private function processForm($form, $season, $params, $externalLineId)
    {
        $this->get('canal_tp_mtt.line_manager')->save(
            $form->getData(),
            $season,
            $externalLineId
        );
        $this->get('session')->getFlashBag()->add(
            'success',
            $this->get('translator')->trans(
                'line.layout_chosen',
                array(),
                'default'
            )
        );

        if (empty($params['externalRouteId']))
        {
            return $this->redirectToRoute(
                'canal_tp_mtt_line_timetable_list',
                array(
                    'externalNetworkId' => $params['externalNetworkId'],
                    'externalLineId'    => $params['externalLineId'],
                    'seasonId'          => $season->getId()
                )
            );
        }
        else
        {
            return $this->redirect(
                $this->generateUrl(
                    'canal_tp_mtt_stop_point_list',
                    array(
                        'externalNetworkId' => $params['externalNetworkId'],
                        'externalLineId'    => $params['externalLineId'],
                        'seasonId'          => $season->getId(),
                        'externalRouteId'   => $params['externalRouteId'],
                    )
                )
            );
        }
    }

    /*
     * @function display a form to choose a layout for a given line
     * or save this form and redirects
     */
    public function chooseLayoutAction(
        $externalNetworkId,
        $externalLineId,
        $seasonId,
        $externalRouteId = null
    )
    {
        $this->isGranted('BUSINESS_CHOOSE_LAYOUT');
        $perimeterManager = $this->get('nmm.perimeter_manager');
        $perimeter = $perimeterManager->findOneByExternalNetworkId(
            $this->getUser()->getCustomer(),
            $externalNetworkId
        );
        $season = $this->get('canal_tp_mtt.season_manager')->getSeasonWithPerimeterAndSeasonId(
            $perimeter,
            $seasonId
        );
        $layoutConfigs = $this->get('canal_tp_mtt.layout_config')->findLayoutConfigByCustomer();

        $params = array(
            'externalNetworkId' => $externalNetworkId,
            'externalLineId'    => $externalLineId,
            'externalRouteId'   => $externalRouteId
        );
        $lineConfig = $this->get('canal_tp_mtt.line_manager')->getLineConfigWithSeasonByExternalLineId(
            $externalLineId,
            $season
        );

        $form = $this->createForm(
            new LineConfigType($layoutConfigs),
            $lineConfig,
            array(
                'action' => $this->getRequest()->getRequestUri()
            )
        );

        $form->handleRequest($this->getRequest());
        if ($form->isValid()) {
            return ($this->processForm($form, $season, $params, $externalLineId));
        } else {
            return $this->render(
                'CanalTPMttBundle:Line:chooseLayout.html.twig',
                array(
                    'form' => $form->createView()
                )
            );
        }
    }
}
