<?php

namespace CanalTP\MttBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use CanalTP\MttBundle\Form\Type\LayoutConfigType;
use CanalTP\MttBundle\Entity\Layout;

/*
 * LayoutController
 */
class LayoutConfigController extends AbstractController
{
    public function listAction($externalNetworkId)
    {
        $this->isGranted('BUSINESS_MANAGE_LAYOUT_CONFIG');
        $layoutConfigRepo = $this->getDoctrine()->getRepository('CanalTPMttBundle:LayoutConfig');

        return $this->render(
            'CanalTPMttBundle:LayoutConfig:list.html.twig',
            array(
                'pageTitle' => 'menu.layouts_manage',
                'layoutConfigs' => $this->get('canal_tp_mtt.layout_config')->findLayoutConfigByCustomer(),
                'externalNetworkId' => $externalNetworkId,
                'no_left_menu' => true
            )
        );
    }

    private function buildForm($externalNetworkId, $layoutConfigId)
    {
        $form = $this->createForm(
            new LayoutConfigType(
                $this->get('canal_tp_mtt.layout')->findByCustomer($this->getUser()->getCustomer())
            ),
            $this->get('canal_tp_mtt.layout_config')->find($layoutConfigId),
            array(
                'action' => $this->generateUrl(
                    'canal_tp_mtt_layout_config_edit',
                    array(
                        'externalNetworkId' => $externalNetworkId,
                        'layoutConfigId' => $layoutConfigId
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
            $this->get('canal_tp_mtt.layout_config')->save($form->getData());
            $this->get('session')->getFlashBag()->add(
                'success',
                $this->get('translator')->trans(
                    'layout_config.created',
                    array(),
                    'default'
                )
            );

            return $this->redirect(
                $this->generateUrl(
                    'canal_tp_mtt_layout_config_list',
                    array('externalNetworkId' => $externalNetworkId)
                )
            );
        }

        return (null);
    }

    public function editAction(Request $request, $externalNetworkId, $layoutConfigId)
    {
        $this->isGranted('BUSINESS_MANAGE_LAYOUT_CONFIG');
        $form = $this->buildForm($externalNetworkId, $layoutConfigId);
        $render = $this->processForm($request, $form, $externalNetworkId);

        if (!$render) {
            return $this->render(
                'CanalTPMttBundle:LayoutConfig:form.html.twig',
                array(
                    'form' => $form->createView(),
                    'title' => ($layoutConfigId ? 'layout_config.edit' : 'layout_config.create'
                    )
                )
            );
        }

        return ($render);
    }

    public function deleteAction(Request $request, $externalNetworkId, $layoutConfigId, $confirm)
    {
        $this->isGranted('BUSINESS_MANAGE_LAYOUT_CONFIG');

        $layoutConfig = $this->get('canal_tp_mtt.layout_config')->find($layoutConfigId);

        if (!is_null($confirm)) {
            $this->get('canal_tp_mtt.layout_config')->delete($layoutConfig);

            return $this->redirect(
                $this->generateUrl(
                    'canal_tp_mtt_layout_config_list',
                    array('externalNetworkId' => $externalNetworkId)
                )
            );
        }

        $extCoverageId = null;
        foreach ($this->get('security.context')->getToken()->getUser()->getCustomer()->getPerimeters() as $value) {
            if ($value->getExternalNetworkId() == $externalNetworkId) {
                $extCoverageId = $value->getExternalCoverageId();
            }
        }

        $extLineIds = array();
        foreach ($layoutConfig->getLineConfigs() as $line) {
            $extLineIds[$line->getExternalLineId()] = $this->get('canal_tp_mtt.navitia')->getLineTitle($extCoverageId, $externalNetworkId, $line->getExternalLineId());

        }

        return $this->render(
            'CanalTPMttBundle:LayoutConfig:delete.html.twig',
            array(
                'lines' => $extLineIds,
                'externalNetworkId' => $externalNetworkId,
                'layoutConfigId' => $layoutConfigId
            )
        );
    }
}
