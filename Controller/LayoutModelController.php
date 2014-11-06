<?php

namespace CanalTP\MttBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use CanalTP\MttBundle\Form\Type\LayoutModelType;
use CanalTP\MttBundle\Entity\Layout;

class LayoutModelController extends AbstractController
{
    public function uploadModelAction(Request $request, $externalNetworkId)
    {
        $form = $this->createForm(
            new LayoutModelType(),
            null,
            array(
                'action' => $this->generateUrl(
                    'canal_tp_mtt_model_upload',
                    array('externalNetworkId' => $externalNetworkId)
                )
            )
        );

        $form->handleRequest($request);

        if ($form->isValid()) {
            try {
                $this->get('canal_tp_mtt.layout_model')->save($form->getData());
            } catch(\Exception $e) {
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
                        'canal_tp_mtt_customer_list',
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
                    'canal_tp_mtt_customer_list',
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
