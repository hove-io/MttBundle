<?php

namespace CanalTP\MttBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use CanalTP\MttBundle\Form\Type\AreaType;

class AreaController extends AbstractController
{
    private $areaManager = null;

    private function buildForm($externalNetworkId, $areaId)
    {
        $perimeter = $this->get('nmm.perimeter_manager')->findOneByExternalNetworkId(
            $this->getUser()->getCustomer(),
            $externalNetworkId
        );
        $form = $this->createForm(
            new AreaType(),
            $this->get('canal_tp_mtt.area_manager')->getAreaWithPerimeter($perimeter, $areaId),
            array(
                'action' => $this->generateUrl(
                    'canal_tp_mtt_area_edit',
                    array(
                        'externalNetworkId' => $externalNetworkId,
                        'areaId' => $areaId
                    )
                )
            )
        );

        return ($form);
    }

    private function processForm(Request $request, $form, $externalNetworkId)
    {
        $form->handleRequest($request);

        if ($form->isValid()) {
            $this->areaManager->save($form->getData(), $this->getUser(), $externalNetworkId);
            $this->get('session')->getFlashBag()->add(
                'success',
                $this->get('translator')->trans(
                    'area.created',
                    array(),
                    'default'
                )
            );

            return $this->redirect(
                $this->generateUrl(
                    'canal_tp_mtt_area_list',
                    array('externalNetworkId' => $externalNetworkId)
                )
            );
        }

        return (null);
    }

    public function editAction(Request $request, $externalNetworkId, $areaId)
    {
        $this->isGranted('BUSINESS_MANAGE_AREA');
        $this->areaManager = $this->get('canal_tp_mtt.area_manager');

        $form = $this->buildForm($externalNetworkId, $areaId);
        $render = $this->processForm($request, $form, $externalNetworkId);
        if (!$render) {
            return $this->render(
                'CanalTPMttBundle:Area:form.html.twig',
                array(
                    'form' => $form->createView(),
                    'title' => ($areaId ? 'area.edit' : 'area.create')
                )
            );
        }

        return ($render);
    }

    public function listAction($externalNetworkId)
    {
        $this->isGranted(array('BUSINESS_LIST_AREA', 'BUSINESS_MANAGE_AREA'));
        $perimeter = $this->get('nmm.perimeter_manager')->findOneByExternalNetworkId(
            $this->getUser()->getCustomer(),
            $externalNetworkId
        );

        return $this->render(
            'CanalTPMttBundle:Area:list.html.twig',
            array(
                'areas' => $this->get('canal_tp_mtt.area_manager')->findByPerimeter($perimeter),
                'externalNetworkId' => $externalNetworkId
            )
        );
    }

    public function removeAction($externalNetworkId, $areaId)
    {
        $this->isGranted('BUSINESS_MANAGE_AREA');
        $areaManager = $this->get('canal_tp_mtt.area_manager');
        $perimeter = $this->get('nmm.perimeter_manager')->findOneByExternalNetworkId(
            $this->getUser()->getCustomer(),
            $externalNetworkId
        );

        $areaManager->remove($areaId);
        $this->get('session')->getFlashBag()->add(
            'success',
            $this->get('translator')->trans(
                'area.deleted',
                array(),
                'default'
            )
        );

        return $this->render(
            'CanalTPMttBundle:Area:list.html.twig',
            array(
                'areas' => $areaManager->findByPerimeter($perimeter),
                'externalNetworkId' => $externalNetworkId
            )
        );
    }

    // pdf
    public function listPdfAction($areaId)
    {
        $this->isGranted(array('BUSINESS_LIST_AREA', 'BUSINESS_MANAGE_AREA'));

        $area = $this->get('canal_tp_mtt.area_manager')->find($areaId);
        $seasons = $this->get('canal_tp_mtt.season_manager')->findByPerimeter(
            $area->getPerimeter()
        );

        return $this->render(
            'CanalTPMttBundle:Area:listPdf.html.twig',
            array(
                'area'      => $area,
                'seasons'   => $seasons,
                'areaPdf'   => $area->getAreasPdf(),
            )
        );
    }

    public function editStopsAction($externalNetworkId, $areaId)
    {
        $this->isGranted(array('BUSINESS_LIST_AREA', 'BUSINESS_MANAGE_AREA'));

        $area = $this->get('canal_tp_mtt.area_manager')->find($areaId);
        $perimeter = $this->get('nmm.perimeter_manager')->findOneByExternalNetworkId(
            $this->getUser()->getCustomer(),
            $externalNetworkId
        );

        $stopPointManager = $this->get('canal_tp_mtt.stop_point_manager');
        $stopPointsList = null;
        $stopPointsArea = $area->getStopPoints();
        if (!empty($stopPointsArea)) {
            $stopPointsList = $stopPointManager->enrichStopPoints($area->getStopPoints(), $perimeter->getExternalCoverageId(), $perimeter->getExternalNetworkId());
        }

        return $this->render(
            'CanalTPMttBundle:Area:editStops.html.twig',
            array(
                'area'              => $area,
                'externalNetworkId' => $externalNetworkId,
                'stopPointsList'    => $stopPointsList
            )
        );
    }

    public function navigationAction($externalNetworkId)
    {
        $mttNavitia = $this->get('canal_tp_mtt.navitia');
        $perimeterManager = $this->get('nmm.perimeter_manager');
        $perimeter = $perimeterManager->findOneByExternalNetworkId(
            $this->getUser()->getCustomer(),
            $externalNetworkId
        );
        try {
            $result = $mttNavitia->findAllLinesByMode(
                $perimeter->getExternalCoverageId(),
                $perimeter->getExternalNetworkId()
            );
        } catch(\Exception $e) {
            $errorMessage = $e->getMessage();
            $result = array();
            $this->get('session')->getFlashBag()->add(
                'danger',
                $errorMessage
            );
        }
        return $this->render(
            'CanalTPMttBundle:Area:navigation.html.twig',
            array(
                'result' => $result,
                'externalNetworkId' => $externalNetworkId
            )
        );
    }

    public function saveAction($externalNetworkId, $areaId)
    {
        $stopPoints = $this->get('request')->request->get(
            'stopPoints',
            array()
        );

        if (!empty($stopPoints)) {
            $area = $this->get('canal_tp_mtt.area_manager')->find($areaId);
            $area->setStopPoints($stopPoints);
            $this->getDoctrine()->getManager()->flush($area);
            $this->get('session')->getFlashBag()->add(
                'success',
                $this->get('translator')->trans(
                    'area.confirm_order_saved',
                    array(),
                    'default'
                )
            );
        }

        return $this->redirect(
            $this->generateUrl(
                'canal_tp_mtt_area_edit_stops',
                array(
                    'externalNetworkId' => $externalNetworkId,
                    'areaId'            => $areaId,
                )
            )
        );
    }
}
