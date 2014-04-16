<?php

namespace CanalTP\MttBundle\Controller;

use CanalTP\MttBundle\Form\Type\FrequenciesType;
use CanalTP\MttBundle\Entity\Block;
use CanalTP\MttBundle\Entity\Frequency;
/*
 * FrequencyController
 */
class FrequencyController extends AbstractController
{
    public function editAction($blockId, $externalNetworkId)
    {
        $blockManager = $this->get('canal_tp_mtt.block_manager');

        $block = $blockManager->findBlock($blockId);
        if (!$block) {
            throw $this->createNotFoundException('Block not found');
        }
        // store frequencies in db
        foreach ($block->getFrequencies() as $frequency) {
            $originalFrequencies[] = $frequency;
        }
        $form = $this->createForm(
            new FrequenciesType($block),
            $block,
            array(
                'action' => $this->getRequest()->getRequestUri()
            )
        );
        $form->handleRequest($this->getRequest());
        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            if (isset($originalFrequencies) && !empty($originalFrequencies)) {
                // filter $originalFrequencies to keep only frequencies in form
                foreach ($block->getFrequencies() as $frequency) {
                    foreach ($originalFrequencies as $key => $toDel) {
                        if ($toDel->getId() === $frequency->getId()) {
                            unset($originalFrequencies[$key]);
                        }
                    }
                }
                foreach ($originalFrequencies as $frequency) {
                    $em->remove($frequency);
                }
            }
            $em->flush();

            return $this->redirect(
                $this->generateUrl(
                    'canal_tp_mtt_timetable_edit',
                    array(
                        'externalNetworkId'     => $externalNetworkId,
                        'seasonId'              => $block->getTimetable()->getLineConfig()->getSeason()->getId(),
                        'externalLineId'        => $block->getTimetable()->getLineConfig()->getExternalLineId(),
                        'externalRouteId'       => $block->getTimetable()->getExternalRouteId(),
                        'externalStopPointId'   => null
                    )
                )
            );
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
