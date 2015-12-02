<?php

namespace CanalTP\MttBundle\Controller;

use CanalTP\MttBundle\Entity\Block;
use CanalTP\MttBundle\Entity\BlockRepository;

class BlockController extends AbstractController
{
    /**
     * Editing/persisting a block from a timetable object
     *
     * @param string $externalNetworkId
     * @param integer $timetableId
     * @param string $blockType
     * @param string $domId
     */
    public function editAction(
        $externalNetworkId,
        $timetableId,
        $blockType,
        $domId
    )
    {
        $blockTypeFactory = $this->get('canal_tp_mtt.form.factory.block');
        $stopTimetableManager = $this->get('canal_tp_mtt.stop_timetable_manager');
        $perimeterManager = $this->get('nmm.perimeter_manager');

        $perimeter = $perimeterManager->findOneByExternalNetworkId(
            $this->getUser()->getCustomer(),
            $externalNetworkId
        );
        $stopTimetable = $stopTimetableManager->getStopTimetableById(
            $timetableId,
            $perimeter->getExternalCoverageId()
        );

        $data = array(
            'domId' => $domId,
            'type'  => $blockType
        );

        $block = $stopTimetable->getBlockByDomId($domId);

        if (empty($block)) {
            $block = new Block();
            $block->setStopTimetable($stopTimetable);
        }

        $blockTypeFactory->init(
            $blockType,
            $data,
            $block,
            $perimeter->getExternalCoverageId()
        );

        $form = $blockTypeFactory->buildForm()
            ->setAction($this->getRequest()->getRequestUri())
            ->setMethod('POST')->getForm();
        $form->handleRequest($this->getRequest());
        if ($form->isValid()) {
            $blockTypeFactory->buildHandler()->process($form->getData(), $stopTimetable);

            return $this->redirect(
                $this->generateUrl(
                    'canal_tp_mtt_stop_timetable_edit',
                    array(
                        'externalNetworkId'     => $externalNetworkId,
                        'seasonId'              => $stopTimetable->getLineConfig()->getSeason()->getId(),
                        'externalLineId'        => $stopTimetable->getLineConfig()->getExternalLineId(),
                        'externalRouteId'       => $stopTimetable->getExternalRouteId()
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

    public function deleteAction($timetableId, $blockId, $externalNetworkId)
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
                    array('%blockid%'=>$blockId), 'exceptions'
                )
            );
        } else {
            $this->getDoctrine()->getEntityManager()->remove($block);
            $this->getDoctrine()->getEntityManager()->flush();
        }
        $stopTimetable = $stopTimetableManager->getStopTimetableById(
            $timetableId,
            $perimeter->getExternalCoverageId()
        );

        return $this->redirect(
            $this->generateUrl(
                'canal_tp_mtt_stop_timetable_edit',
                array(
                    'externalNetworkId'     => $externalNetworkId,
                    'seasonId'              => $stopTimetable->getLineConfig()->getSeason()->getId(),
                    'externalLineId'        => $stopTimetable->getLineConfig()->getExternalLineId(),
                    'externalRouteId'       => $stopTimetable->getExternalRouteId()
                )
            )
        );
    }
}
