<?php

namespace CanalTP\MttBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use CanalTP\MttBundle\Entity\Block;
use CanalTP\MttBundle\Entity\BlockRepository;
use CanalTP\MttBundle\Entity\Timetable;
use CanalTP\MttBundle\Entity\LineTimetable;
use CanalTP\MttBundle\Entity\StopTimetable;
use CanalTP\MttBundle\Form\Type\Block\BlockType;

class BlockController extends AbstractController
{
    /**
     * Editing/persisting a block from a timetable object
     *
     * @param string $externalNetworkId
     * @param integer $timetableId
     * @param string $type
     * @param integer $rank
     */
    public function addAction(Request $request, $externalNetworkId, $timetableId, $type, $rank)
    {
        $this->isGranted(
            array(
                'BUSINESS_MANAGE_LINE_TIMETABLE',
                'BUSINESS_MANAGE_STOP_TIMETABLE'
            )
        );

        $blockManager = $this->get('canal_tp_mtt.block_manager');
        $timetableManager = $this->get(Timetable::$managers[$type]);
        $timetable = $timetableManager->find($timetableId);

        $data = array(
            'rank'  => $rank,
            'type'  => $type,
            'domId' => null
        );

        $block = $blockManager->findOrCreate(-1, $timetable, $data);

        $form = $this->createForm(
            new BlockType(),
            $block,
            array(
                'action' => $this->generateUrl(
                    'canal_tp_mtt_block_add',
                    array(
                        'externalNetworkId' => $externalNetworkId,
                        'timetableId' => $timetableId,
                        'type' => $type,
                        'rank' => $rank
                    )
                ),
                'em' => $this->get('doctrine.orm.entity_manager')
            )
        );

        $form->handleRequest($request);
        if ($form->isValid()) {
            $blockManager = $this->get('canal_tp_mtt.block_manager');
            $block = $form->getData();
            $blockManager->save($block, $form['number']->getData());

            if ($timetable instanceof StopTimetable) {
                return $this->redirect(
                    $this->generateUrl(
                        'canal_tp_mtt_stop_timetable_edit',
                        array(
                            'externalNetworkId'     => $externalNetworkId,
                            'seasonId'              => $timetable->getLineConfig()->getSeason()->getId(),
                            'externalLineId'        => $timetable->getLineConfig()->getExternalLineId(),
                            'externalRouteId'       => $timetable->getExternalRouteId()
                        )
                    )
                );
            } elseif ($timetable instanceof LineTimetable) {
                return $this->redirect(
                    $this->generateUrl(
                        'canal_tp_mtt_line_timetable_render',
                        array(
                            'externalNetworkId' => $externalNetworkId,
                            'seasonId'          => $timetable->getLineConfig()->getSeason()->getId(),
                            'externalLineId'    => $timetable->getLineConfig()->getExternalLineId(),
                            'mode'              => 'edit'
                        )
                    )
                );
            }
        }

        return $this->render(
            'CanalTPMttBundle:Block:form.html.twig',
            array(
                'form' => $form->createView(),
            )
        );
    }

    public function autoCreateAction(
        Request $request,
        $externalNetworkId,
        $timetableId,
        $type,
        $blockType,
        $domId,
        $rank
    ) {
        $this->isGranted(
            array(
                'BUSINESS_MANAGE_LINE_TIMETABLE',
                'BUSINESS_MANAGE_STOP_TIMETABLE'
            )
        );

        $this->isPostAjax($request);

        $timetableManager = $this->get(Timetable::$managers[$type]);
        $timetable = $timetableManager->find($timetableId);

        $perimeter = $this->get('nmm.perimeter_manager')->findOneByExternalNetworkId(
            $this->getUser()->getCustomer(),
            $externalNetworkId
        );

        // checking that the provided block is a calendar with data and is accessible via the network
        if (empty($timetable)) {
            return $this->prepareJsonResponse(
                $this->get('translator')->trans(
                    'line_timetable.error.block_not_linked',
                    array('%blockId%' => 0)
                ),
                JsonResponse::HTTP_UNPROCESSABLE_ENTITY
            );
        }
        if ($timetable->getLineConfig()->getSeason()->getPerimeter() != $perimeter) {
            return $this->prepareJsonResponse(
                $this->get('translator')->trans(
                    'line_timetable.error.bad_external_network',
                    array('%externalNetworkId%' => $externalNetworkId)
                ),
                JsonResponse::HTTP_UNAUTHORIZED
            );
        }

        $data = array(
            'type'  => $blockType,
            'rank'  => $rank,
            'domId' => $domId
        );

        $blockManager = $this->get('canal_tp_mtt.block_manager');
        $block = $blockManager->findOrCreate(-1, $timetable, $data);

        $blockManager->save($block);

        return new Response();
    }

    /**
     * returns form for a given block type
     * or save content of the block using Form factory
     */
    public function editAction(
        Request $request,
        $externalNetworkId,
        $timetableId,
        $type,
        $blockId,
        $blockType,
        $domId,
        $rank
    ) {
        $this->isGranted(
            array(
                'BUSINESS_MANAGE_LINE_TIMETABLE',
                'BUSINESS_MANAGE_STOP_TIMETABLE'
            )
        );

        $timetableManager = $this->get(Timetable::$managers[$type]);
        $timetable = $timetableManager->find($timetableId);

        $blockTypeFactory = $this->get('canal_tp_mtt.form.factory.block');
        $blockManager = $this->get('canal_tp_mtt.block_manager');

        $perimeterManager = $this->get('nmm.perimeter_manager');

        $perimeter = $perimeterManager->findOneByExternalNetworkId(
            $this->getUser()->getCustomer(),
            $externalNetworkId
        );

        $data = array(
            'type'  => $blockType,
            'rank'  => $rank,
            'domId' => $domId
        );

        $block = $blockManager->findOrCreate($blockId, $timetable, $data);

        $blockTypeFactory->init(
            $blockType,
            $data,
            $block,
            $perimeter->getExternalCoverageId()
        );

        $form = $blockTypeFactory->buildForm()
            ->setAction($this->getRequest()->getRequestUri())
            ->setMethod('POST')->getForm();

        $form->handleRequest($request);

        if ($form->isValid()) {
            $blockTypeFactory->buildHandler()->process($form->getData(), $timetable);

            if ($timetable instanceof StopTimetable) {
                return $this->redirectToRoute(
                    'canal_tp_mtt_stop_timetable_edit',
                    array(
                        'externalNetworkId' => $externalNetworkId,
                        'seasonId'          => $timetable->getLineConfig()->getSeason()->getId(),
                        'externalLineId'    => $timetable->getLineConfig()->getExternalLineId(),
                        'externalRouteId'   => $timetable->getExternalRouteId()
                    )
                );
            } elseif ($timetable instanceof LineTimetable) {
                return $this->redirectToRoute(
                    'canal_tp_mtt_line_timetable_render',
                    array(
                        'externalNetworkId' => $externalNetworkId,
                        'seasonId'          => $timetable->getLineConfig()->getSeason()->getId(),
                        'externalLineId'    => $timetable->getLineConfig()->getExternalLineId(),
                        'mode'              => 'edit'
                    )
                );
            }
        }

        return $this->render(
            'CanalTPMttBundle:Block:get_form.html.twig',
            array(
                'form' => $form->createView(),
            )
        );
    }

    /**
     * Deleting a block from a Timetable
     *
     * @param string $externalNetworkId
     * @param integer $timetableId
     * @param string $type
     * @param integer $blockId
     */
    public function deleteAction(
        $externalNetworkId,
        $timetableId,
        $type,
        $blockId
    ) {
        $this->isGranted(
            array(
                'BUSINESS_MANAGE_LINE_TIMETABLE',
                'BUSINESS_MANAGE_STOP_TIMETABLE'
            )
        );

        $perimeterManager = $this->get('nmm.perimeter_manager');
        $perimeter = $perimeterManager->findOneByExternalNetworkId(
            $this->getUser()->getCustomer(),
            $externalNetworkId
        );

        $timetableManager = $this->get(Timetable::$managers[$type]);
        $timetable = $timetableManager->find($timetableId);

        if ($timetable->getLineConfig()->getSeason()->getPerimeter() !== $perimeter) {
            throw new Exception('This timetable is not accessible via the network: ' . $externalNetworkId);
        }

        $block = $timetable->getBlockById($blockId);

        if (empty($block)) {
            throw new Exception('The block ' . $blockId . ' is not linked to Timetable ' . $timetableId);
        }

        if (!$block) {
            throw $this->createNotFoundException(
                $this->get('translator')->trans(
                    'controller.block.delete.block_not_found',
                    array('%blockid%'=>$blockId), 'exceptions'
                )
            );
        } else {
            $this->get('canal_tp_mtt.block_manager')->delete($block);
        }

        if ($timetable instanceof StopTimetable) {
            return $this->redirectToRoute(
                'canal_tp_mtt_stop_timetable_edit',
                array(
                    'externalNetworkId' => $externalNetworkId,
                    'seasonId'          => $timetable->getLineConfig()->getSeason()->getId(),
                    'externalLineId'    => $timetable->getLineConfig()->getExternalLineId(),
                    'externalRouteId'   => $timetable->getExternalRouteId()
                )
            );
        } elseif ($timetable instanceof LineTimetable) {
            return $this->redirectToRoute(
                'canal_tp_mtt_line_timetable_render',
                array(
                    'externalNetworkId' => $externalNetworkId,
                    'seasonId'          => $timetable->getLineConfig()->getSeason()->getId(),
                    'externalLineId'    => $timetable->getLineConfig()->getExternalLineId(),
                    'mode'              => 'edit'
                )
            );
        }
    }
}
