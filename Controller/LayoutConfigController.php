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
        $this->isGranted(array('BUSINESS_LIST_LAYOUT_CONFIG', 'BUSINESS_MANAGE_LAYOUT_CONFIG'));
        $layoutConfigRepo = $this->getDoctrine()->getRepository('CanalTPMttBundle:LayoutConfig');

        return $this->render(
            'CanalTPMttBundle:LayoutConfig:list.html.twig',
            array(
                'pageTitle' => 'menu.layouts_manage',
                'layoutConfigs' => $this->get('canal_tp_mtt.layout_config')->findLayoutConfigByCustomer(),
                'externalNetworkId' => $externalNetworkId,
                'layoutConfigRepo' => $layoutConfigRepo
            )
        );
    }


    private function buildForm($externalNetworkId, $layoutConfigId)
    {
        $form = $this->createForm(
            new LayoutConfigType(
                $this->get('canal_tp_mtt.layout')->findAll()
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
                    'title' => ($layoutConfigId ? 'layout_config.edit' : 'layout_config.create')
                )
            );
        }

        return ($render);
    }
}
