<?php

namespace CanalTP\MttBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use CanalTP\MttBundle\Entity\LineConfig;

/*
 * LineController
 */
class LineController extends Controller
{
    /*
     * @function process a form to save a layout for a line and a season. Insert a lineConfig element in bdd if needed.
     */
    private function processForm($form, $LineConfig, $season, $params)
    {
        if (empty($LineConfig)) {
            $data = $form->getData();
            $LineConfig = new LineConfig();
            $LineConfig->setExternalLineId($params['line_id']);
            $LineConfig->setLayout($data['layout']);
            $LineConfig->setSeason($season);
        }
        if ($LineConfig->getLayout() != null) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($LineConfig);
            $em->flush();

            $this->get('session')->getFlashBag()->add(
                'notice',
                $this->get('translator')->trans('line.layout_chosen', array(), 'default')
            );
        }

        return $this->redirect($this->generateUrl('canal_tp_meth_stop_point_list', array(
            'network_id'        => $params['externalNetworkId'],
            'line_id'           => $params['line_id'],
            'seasonId'          => $season->getId(),
            'externalRouteId'   => $params['externalRouteId'],
        )));
    }

    /*
     * @function display a form to choose a layout for a given line or save this form and redirects
     */
    public function chooseLayoutAction($externalNetworkId, $line_id, $seasonId, $externalRouteId)
    {
        $season = $this->get('canal_tp_mtt.season_manager')->getSeasonWithNetworkIdAndSeasonId($externalNetworkId, $seasonId);

        $params = array('externalNetworkId' => $externalNetworkId,
                        'line_id'           => $line_id,
                        'externalRouteId'   => $externalRouteId);
                        
        $lineConfig = $this->getDoctrine()
            ->getRepository('CanalTPMttBundle:LineConfig')
            ->findOneBy(
                array(
                    'externalLineId' => $line_id,
                    'season' => $season
                )
            );

        $form = $this->createFormBuilder($lineConfig)
            ->add(
                'layout',
                'layout',
                array(
                    'empty_value' => 'Choose a layout',
                )
            )
            ->setAction($this->getRequest()->getRequestUri())
            ->setMethod('POST')
            ->getForm();

        $form->handleRequest($this->getRequest());
        if ($form->isValid()) {
            return ($this->processForm($form, $lineConfig, $season, $params));
        } else {
            return $this->render(
                'CanalTPMttBundle:Line:chooseLayout.html.twig',
                array(
                    'form'        => $form->createView()
                )
            );
        }
    }
}
