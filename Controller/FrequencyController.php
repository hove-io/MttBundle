<?php

namespace CanalTP\MttBundle\Controller;

use CanalTP\MttBundle\Form\Type\FrequenciesType;
use CanalTP\MttBundle\Entity\Block;
use CanalTP\MttBundle\Entity\Timetable;
use CanalTP\MttBundle\Entity\LineTimecard;
use CanalTP\MttBundle\Entity\Frequency;
/*
 * FrequencyController
 */
class FrequencyController extends AbstractController
{
    /**
     * Opens the frequency editing form
     *
     * @param integer $blockId Block id
     * @param $objectType associated object at block
     * @param string $externalNetworkId Network Id
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function editAction($blockId, $objectType, $externalNetworkId)
    {
        /** @var \CanalTP\MttBundle\Services\BlockManager $blockManager */
        $blockManager = $this->get('canal_tp_mtt.block_manager');

        /** @var \CanalTP\MttBundle\Entity\Block $block */
        $block = $blockManager->findBlock($blockId);
        if (!$block) {
            throw $this->createNotFoundException('Block not found');
        }
        // store frequencies in db
        foreach ($block->getFrequencies() as $frequency) {
            $originalFrequencies[] = $frequency;
        }

        $form = $this->createForm(
            new FrequenciesType($block, $objectType),
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

            if ($objectType == Timetable::OBJECT_TYPE) {
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
            } elseif ($objectType == LineTimecard::OBJECT_TYPE) {
                return $this->redirect(
                    $this->generateUrl(
                        'canal_tp_mtt_timecard_edit_layout',
                        array(
                            'externalNetworkId' => $externalNetworkId,
                            'seasonId' => $block->getLineTimecard()->getLineConfig()->getSeason()->getId(),
                            'externalLineId' => $block->getLineTimecard()->getLineConfig()->getExternalLineId()
                        )
                    )
                );
            }
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
