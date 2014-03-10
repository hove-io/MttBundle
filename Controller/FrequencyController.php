<?php

namespace CanalTP\MttBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use CanalTP\MttBundle\Form\Type\FrequencyType;
/*
 * CalendarController
 */
class FrequencyController extends Controller
{
    public function editAction($blockId, $layoutId)
    {
        $frequencyManager = $this->get('canal_tp_mtt.frequency_manager');
        $layoutsConfig = $this->container->getParameter('layouts');
        
        $frequencies = $frequencyManager->getByBlockId($blockId);
        $forms = array();
        if (empty($frequencies)) {
            $forms[] = $this->createForm(
                new FrequencyType($layoutsConfig[$layoutId], $this->getRequest()->getRequestUri()), 
                $frequencyManager->getEntity($blockId)
            )
            ->createView();
        }
        
        return $this->render(
            'CanalTPMttBundle:Frequency:form.html.twig',
            array(
                'forms'        => $forms,
            )
        );
    }
}