<?php

namespace CanalTP\MttBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use CanalTP\MttBundle\Form\Type\LayoutModelType;

class LayoutModelController extends AbstractController
{
    public function listAction(Request $request, $externalNetworkId)
    {
        $this->isGranted('BUSINESS_MANAGE_LAYOUT_MODEL');

        return $this->render(
            'CanalTPMttBundle:LayoutModel:list.html.twig',
            array(
                'externalNetworkId' => $externalNetworkId,
                'layouts' => $this->get('canal_tp_mtt.layout')->findAll(),
                'no_left_menu' => true
            )
        );
    }

    public function uploadModelAction(Request $request, $externalNetworkId)
    {
        $this->isGranted('BUSINESS_MANAGE_LAYOUT_MODEL');
        $id = $request->query->get('id');

        $layout = empty($id)
            ? new \CanalTP\MttBundle\Entity\Layout()
            : $this->get('canal_tp_mtt.layout')->findById($id);


        $form = $this->createForm(
            new LayoutModelType(),
            $layout,
            array(
                'action' => $this->generateUrl(
                    'canal_tp_mtt_model_upload',
                    array(
                        'externalNetworkId' => $externalNetworkId,
                        'id' => $id
                    )
                )
            )
        );

        $form->handleRequest($request);

        if ($form->isValid()) {
            try {
                $this->get('canal_tp_mtt.layout_model')->save($form->getData());
            } catch (\Exception $e) {
                $this->get('session')->getFlashBag()->add(
                    'danger',
                    $this->get('translator')->trans(
                        $e->getMessage(),
                        array(),
                        'default'
                    )
                );

                return $this->redirect(
                    $this->generateUrl(
                        'canal_tp_mtt_model_list',
                        array('externalNetworkId' => $externalNetworkId)
                    )
                );
            }
            $this->get('session')->getFlashBag()->add(
                'success',
                $this->get('translator')->trans(
                    'layout_model.uploaded',
                    array(),
                    'default'
                )
            );

            return $this->redirect(
                $this->generateUrl(
                    'canal_tp_mtt_model_list',
                    array('externalNetworkId' => $externalNetworkId)
                )
            );
        }

        return $this->render(
            'CanalTPMttBundle:LayoutModel:form.html.twig',
            array(
                'form' => $form->createView(),
                'title' => 'layout_model.upload',
                'externalNetworkId' => $externalNetworkId
            )
        );
    }
}
