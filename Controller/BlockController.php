<?php

namespace CanalTP\MttBundle\Controller;

use CanalTP\MttBundle\Entity\Block;
use CanalTP\MttBundle\Entity\LineTimecard;
use CanalTP\MttBundle\Entity\Timetable;
use Symfony\Component\Config\Definition\Exception\Exception;

class BlockController extends AbstractController
{
    /**
     * returns form for a given block type
     * or save content of the block using Form factory
     */
    public function editAction(
        $externalNetworkId,
        $dom_id,
        $objectType,
        $objectId,
        $block_type = 'text',
        $stop_point = null
    )
    {
        $objectManager = $this->getObjectManager($objectType);

        $blockTypeFactory = $this->get('canal_tp_mtt.form.factory.block');

        $blockManager = $this->get('canal_tp_mtt.block_manager');
        $perimeterManager = $this->get('nmm.perimeter_manager');

        $perimeter = $perimeterManager->findOneByExternalNetworkId(
            $this->getUser()->getCustomer(),
            $externalNetworkId
        );


        $object = $objectManager->getById(
            $objectId,
            $perimeter->getExternalCoverageId()
        );

        $data = array(
            'dom_id' => $dom_id,
            'type_id' => $block_type,
            'stop_point' => $stop_point
        );

        $block = $blockManager->getBlock($dom_id, $objectId, $objectType, $stop_point);

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

        if ($form->isValid()) {
            $blockTypeFactory->buildHandler()->process($form->getData(), $object);

            if ($objectType == Timetable::OBJECT_TYPE) {
                return $this->redirect(
                    $this->generateUrl(
                        'canal_tp_mtt_timetable_edit',
                        array(
                            'externalNetworkId' => $externalNetworkId,
                            'seasonId' => $object->getLineConfig()->getSeason()->getId(),
                            'externalLineId' => $object->getLineConfig()->getExternalLineId(),
                            'externalRouteId' => $object->getExternalRouteId(),
                            'externalStopPointId' => $stop_point
                        )
                    )
                );
            } else if($objectType == LineTimecard::OBJECT_TYPE) {
                return $this->redirect(
                    $this->generateUrl(
                        'canal_tp_mtt_timecard_edit_layout',
                        array(
                            'externalNetworkId' => $externalNetworkId,
                            'seasonId' => $object->getLineConfig()->getSeason()->getId(),
                            'externalLineId' => $object->getLineConfig()->getExternalLineId()
                        )
                    )
                );
            }
        }

        return $this->render(
            'CanalTPMttBundle:Block:get_form.html.twig',
            array(
                'form'        => $form->createView(),
            )
        );
    }

    public function deleteAction($objectType, $objectId, $blockId, $externalNetworkId)
    {
        $perimeterManager = $this->get('nmm.perimeter_manager');
        $perimeter = $perimeterManager->findOneByExternalNetworkId(
            $this->getUser()->getCustomer(),
            $externalNetworkId
        );


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

        $objectManager = $this->getObjectManager($objectType);
        $object = $objectManager->getById(
            $objectId,
            $perimeter->getExternalCoverageId()
        );

        if ($objectType == Timetable::OBJECT_TYPE) {
            return $this->redirect(
                $this->generateUrl(
                    'canal_tp_mtt_timetable_edit',
                    array(
                        'externalNetworkId' => $externalNetworkId,
                        'seasonId' => $object->getLineConfig()->getSeason()->getId(),
                        'externalLineId' => $object->getLineConfig()->getExternalLineId(),
                        'externalRouteId' => $object->getExternalRouteId(),
                        'externalStopPointId' =>null
                    )
                )
            );
        } else if($objectType == LineTimecard::OBJECT_TYPE) {
            return $this->redirect(
                $this->generateUrl(
                    'canal_tp_mtt_timecard_edit_layout',
                    array(
                        'externalNetworkId' => $externalNetworkId,
                        'seasonId' => $object->getLineConfig()->getSeason()->getId(),
                        'externalLineId' => $object->getLineConfig()->getExternalLineId()
                    )
                )
            );
        }

    }

    private function getObjectManager($object) {
        switch($object) {
            case 'timetable':
                $service = 'canal_tp_mtt.timetable_manager';
                break;
            case 'lineTimecard':
                $service = 'canal_tp_mtt.line_timecard_manager';
                break;
            default:
                throw new Exception('Object not supported');
                break;
        }

        return $this->get($service);
    }
}
