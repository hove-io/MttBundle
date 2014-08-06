<?php

namespace CanalTP\MttBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use CanalTP\MttBundle\Form\Type\AreaType;

class AreaController extends AbstractController
{
    private $areaManager = null;

    private function buildForm($externalNetworkId, $areaId)
    {
        $form = $this->createForm(
            new AreaType(),
            $this->get('canal_tp_mtt.area_manager')->getAreaWithExternalNetworkId($externalNetworkId, $areaId),
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
            $this->areaManager->save($form->getData(), $externalNetworkId);
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

        return $this->render(
            'CanalTPMttBundle:Area:list.html.twig',
            array(
                'areas' => $this->get('canal_tp_mtt.area_manager')->findByExternalNetworkId($externalNetworkId),
                'externalNetworkId' => $externalNetworkId
            )
        );
    }

    public function removeAction($externalNetworkId, $areaId)
    {
        $this->isGranted('BUSINESS_MANAGE_AREA');
        $areaManager = $this->get('canal_tp_mtt.area_manager');

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
                'areas' => $areaManager->findByExternalNetworkId($externalNetworkId),
                'externalNetworkId' => $externalNetworkId
            )
        );
    }

    // pdf
    public function listPdfAction($areaId)
    {
        $this->isGranted(array('BUSINESS_LIST_AREA', 'BUSINESS_MANAGE_AREA'));

        $area = $this->get('canal_tp_mtt.area_manager')->find($areaId);
        
        return $this->render(
            'CanalTPMttBundle:Area:listPdf.html.twig',
            array(
                'area'      => $area,
                'seasons'   => $area->getNetwork()->getSeasons(),
                'areaPdf'   => $area->getAreasPdf(),
            )
        );
    }
}
