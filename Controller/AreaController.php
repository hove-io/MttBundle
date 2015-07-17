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
                'pageTitle' => 'menu.area_manage',
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
    public function listPdfAction($externalNetworkId, $areaId)
    {
        $this->isGranted(array('BUSINESS_LIST_AREA', 'BUSINESS_MANAGE_AREA'));

        $this->saveList($areaId);
        $area = $this->get('canal_tp_mtt.area_manager')->find($areaId);
        $seasons = $this->get('canal_tp_mtt.season_manager')->findByPerimeter(
            $area->getPerimeter()
        );

        return $this->render(
            'CanalTPMttBundle:Area:listPdf.html.twig',
            array(
                'area'                  => $area,
                'seasons'               => $seasons,
                'areaPdf'               => $area->getAreasPdf(),
                'areaExternalNetworkId' => $area->getPerimeter()->getExternalNetworkId(),
                'externalNetworkId'     => $externalNetworkId
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
                'pageTitle'         => $area->getLabel(),
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
        } catch (\Exception $e) {
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

    private function saveList($areaId)
    {
        $areaStopPoints = $this->get('request')->request->get(
            'stopPoints',
            array()
        );
        $getAllStopPoints = !empty($areaStopPoints);

        if ($getAllStopPoints) {
            $area = $this->get('canal_tp_mtt.area_manager')->find($areaId);

            $area->setStopPoints($areaStopPoints);
            $this->getDoctrine()->getManager()->flush($area);
        }

        return ($getAllStopPoints);
    }

    public function saveAction($externalNetworkId, $areaId)
    {
        if ($this->saveList($areaId)) {
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

    public function generatePdfAction($externalNetworkId, $seasonId, $areaId)
    {
        $areaManager = $this->get('canal_tp_mtt.area_manager');
        $areaPdfManager = $this->get('canal_tp_mtt.area_pdf_manager');
        $seasonManager = $this->get('canal_tp_mtt.season_manager');
        $pdfPayloadGenerator = $this->get('canal_tp_mtt.pdf_payload_generator');
        $amqpPdfGenPublisher = $this->get('canal_tp_mtt.amqp_pdf_gen_publisher');

        $season = $seasonManager->find($seasonId);
        $area = $areaManager->find($areaId);
        try {
            if (!$area->hasStopPoints()) {
                throw new \Exception(
                    $this->get('translator')->trans(
                        'area.stop_point.empty',
                        array(
                            '%areaName%' => $area->getLabel(),
                            '%seasonName%' => $season->getTitle()
                        ),
                        'default'
                    )
                );
            }
            $areaPdf = $areaPdfManager->getAreaPdf($area, $season);
            $payloads = $pdfPayloadGenerator->getAreaPdfPayloads($areaPdf);
            $amqpPdfGenPublisher->publishAreaPdfGen($payloads, $areaPdf);

            $this->get('session')->getFlashBag()->add(
                'success',
                $this->get('translator')->trans(
                    'area.pdf_generation_task_has_started',
                    array(
                        '%jobNumber%' => count($payloads),
                        '%sectorLabel%' => $area->getLabel()
                    ),
                    'default'
                )
            );
        } catch (\Exception $e) {
            $this->get('session')->getFlashBag()->add(
                'danger',
                $this->get('translator')->trans(
                    $e->getMessage(),
                    array(),
                    'exceptions'
                )
            );
        }

        return $this->redirect(
            $this->generateUrl(
                'canal_tp_mtt_homepage',
                array(
                    'externalNetworkId' => $externalNetworkId,
                )
            )
        );
    }
}
