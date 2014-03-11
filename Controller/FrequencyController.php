<?php

namespace CanalTP\MttBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use CanalTP\MttBundle\Form\Type\FrequenciesType;
use CanalTP\MttBundle\Entity\Block;
use CanalTP\MttBundle\Entity\Frequency;
/*
 * FrequencyController
 */
class FrequencyController extends Controller
{
    public function editAction($blockId, $layoutId)
    {
        $blockManager = $this->get('canal_tp_mtt.block_manager');
        $layoutsConfig = $this->container->getParameter('layouts');
        
        $block = $blockManager->findBlock($blockId);
        
        $form = $this->createForm(
            new FrequenciesType($layoutsConfig[$layoutId], $block), 
            $block,
            array(
                'action' => $this->getRequest()->getRequestUri()
            )
        );
        $form->handleRequest($this->getRequest());
        if ($form->isValid()) {
            $this->getDoctrine()->getManager()->flush();
            return $this->redirect($this->generateUrl('default'));
        } else {
            return $this->render(
                'CanalTPMttBundle:Frequency:form.html.twig',
                array(
                    'form'        => $form->createView(),
                )
            );
        }
    }
}