<?php

namespace CanalTP\MttBundle\Controller;

use CanalTP\MttBundle\Entity\Block;

class BlockController extends AbstractController
{
    /**
     * returns form for a given block type
     * or save content of the block using Form factory
     */
    public function editAction(
        $externalNetworkId,
        $dom_id,
        $stopTimetableId,
        $block_type = 'text',
        $stop_point = null
    ) {
    
        $blockTypeFactory = $this->get('canal_tp_mtt.form.factory.block');
        $blockManager = $this->get('canal_tp_mtt.block_manager');
        $stopTimetableManager = $this->get('canal_tp_mtt.stop_timetable_manager');
        $perimeterManager = $this->get('nmm.perimeter_manager');
        $perimeter = $perimeterManager->findOneByExternalNetworkId(
            $this->getUser()->getCustomer(),
            $externalNetworkId
        );
        $data = array(
            'dom_id' => $dom_id,
            'type_id' => $block_type,
            'stop_point' => $stop_point
        );

        $block = $blockManager->getBlock($dom_id, $stopTimetableId, $stop_point);

        $blockTypeFactory->init(
            $block_type,
            $data,
            $block,
            $perimeter->getExternalCoverageId()
        );
        $form = $blockTypeFactory->buildForm()
            ->setAction($this->getRequest()->getRequestUri())
            ->setMethod('POST')->getForm();
        $form->handleRequest($this->getRequest());
        $stopTimetable = $stopTimetableManager->getStopTimetableById(
            $stopTimetableId,
            $perimeter->getExternalCoverageId()
        );
        if ($form->isValid()) {
            $blockTypeFactory->buildHandler()->process($form->getData(), $stopTimetable);

            return $this->redirect(
                $this->generateUrl(
                    'canal_tp_mtt_stop_timetable_edit',
                    array(
                        'externalNetworkId'     => $externalNetworkId,
                        'seasonId'              => $stopTimetable->getLineConfig()->getSeason()->getId(),
                        'externalLineId'        => $stopTimetable->getLineConfig()->getExternalLineId(),
                        'externalRouteId'       => $stopTimetable->getExternalRouteId(),
                        'externalStopPointId'   => $stop_point
                    )
                )
            );
        }

        return $this->render(
            'CanalTPMttBundle:Block:get_form.html.twig',
            array(
                'form'        => $form->createView(),
            )
        );
    }

    public function deleteAction($stopTimetableId, $blockId, $externalNetworkId)
    {
        $perimeterManager = $this->get('nmm.perimeter_manager');
        $perimeter = $perimeterManager->findOneByExternalNetworkId(
            $this->getUser()->getCustomer(),
            $externalNetworkId
        );
        $stopTimetableManager = $this->get('canal_tp_mtt.stop_timetable_manager');
        $repo = $this->getDoctrine()->getRepository('CanalTPMttBundle:Block');

        $block = $repo->find($blockId);
        if (!$block) {
            throw $this->createNotFoundException(
                $this->get('translator')->trans(
                    'controller.block.delete.block_not_found',
                    array('%blockid%'=>$blockId),
                    'exceptions'
                )
            );
        } else {
            $this->getDoctrine()->getEntityManager()->remove($block);
            $this->getDoctrine()->getEntityManager()->flush();
        }
        $stopTimetable = $stopTimetableManager->getStopTimetableById(
            $stopTimetableId,
            $perimeter->getExternalCoverageId()
        );

        return $this->redirect(
            $this->generateUrl(
                'canal_tp_mtt_stop_timetable_edit',
                array(
                    'externalNetworkId'     => $externalNetworkId,
                    'seasonId'              => $stopTimetable->getLineConfig()->getSeason()->getId(),
                    'externalLineId'        => $stopTimetable->getLineConfig()->getExternalLineId(),
                    'externalRouteId'       => $stopTimetable->getExternalRouteId(),
                    'externalStopPointId'   => null
                )
            )
        );
    }
}
