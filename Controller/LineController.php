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

        if (is_null($params['destRoute'])) {
            return $this->redirect(
                $this->generateUrl(
                    'canal_tp_mtt_stop_point_list',
                    array(
                        'externalNetworkId' => $params['externalNetworkId'],
                        'line_id' => $params['line_id'],
                        'seasonId' => $season->getId(),
                        'externalRouteId' => $params['externalRouteId'],
                    )
                )
            );
        } else {
            return $this->redirect(
              $this->generateUrl(
                  'canal_tp_mtt_timecard_list',
                  array(
                      'externalNetworkId' => $params['externalNetworkId'],
                      'lineId' => $params['line_id'],
                  )
              )
            );
        }
    }

    /**
     *
     * Display a form to choose a layout for a given line
     * or save this form and redirects
     *
     * @param $externalNetworkId
     * @param $line_id
     * @param $seasonId
     * @param $externalRouteId
     * @param string $destRoute
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function chooseLayoutAction(
        $externalNetworkId,
        $line_id,
        $seasonId,
        $externalRouteId,
        $destRoute
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
            'line_id'           => $line_id,
            'externalRouteId'   => $externalRouteId,
            'destRoute'         => $destRoute
        );
        $lineConfig = $this->get('canal_tp_mtt.line_manager')->getLineConfigWithSeasonByExternalLineId(
            $line_id,
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
            return ($this->processForm($form, $season, $params, $line_id));
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
