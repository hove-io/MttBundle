<?php

namespace CanalTP\MttBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use CanalTP\MttBundle\Form\Type\FrequenciesType;
use CanalTP\MttBundle\Entity\Block;
use CanalTP\MttBundle\Entity\BlockRepository;
use CanalTP\MttBundle\Entity\Frequency;
use CanalTP\MttBundle\Entity\LineTimetable;
use CanalTP\MttBundle\Entity\StopTimetable;

/*
 * FrequencyController
 */
class FrequencyController extends AbstractController
{
    /**
     * Editing a frequency block
     *
     * @param Request $request
     * @param integer $blockId
     * @param string $externaNetworkId
     */
    public function editAction(Request $request, $blockId, $externalNetworkId)
    {
        $blockManager = $this->get('canal_tp_mtt.block_manager');

        $block = $blockManager->findBlock($blockId);
        if (!$block) {
            throw $this->createNotFoundException('Block not found');
        }

        $form = $this->createForm(
            new FrequenciesType($block),
            $block,
            array(
                'action' => $request->getRequestUri()
            )
        );
        $form->handleRequest($request);

        if ($form->isValid()) {
            $blockManager->update($block);

            if ($block->getTimetable() instanceof StopTimetable) {
                return $this->redirectToRoute(
                    'canal_tp_mtt_stop_timetable_edit',
                    array(
                        'externalNetworkId' => $externalNetworkId,
                        'seasonId'          => $block->getStopTimetable()->getLineConfig()->getSeason()->getId(),
                        'externalLineId'    => $block->getStopTimetable()->getLineConfig()->getExternalLineId(),
                        'externalRouteId'   => $block->getStopTimetable()->getExternalRouteId(),
                    )
                );
            } else if ($block->getTimetable() instanceof LineTimetable) {
                return $this->redirectToRoute(
                    'canal_tp_mtt_line_timetable_render',
                    array(
                        'externalNetworkId' => $externalNetworkId,
                        'externalLineId'    => $block->getLineTimetable()->getLineConfig()->getExternalLineId(),
                        'seasonId'          => $block->getLineTimetable()->getLineConfig()->getSeason()->getId(),
                        'mode'              => 'edit'
                    )
                );
            } else {
                throw new \Exception("This block isn't linked to a Timetable");
            }
        } else {
            return $this->render(
                'CanalTPMttBundle:Frequency:form.html.twig',
                array(
                    'form'              => $form->createView(),
                    'externalNetworkId' => $externalNetworkId,
                    'block'             => $block
                )
            );
        }
    }

    /**
     * Checking chosen frequency is correct for the calendar
     *
     * @param Request $request
     * @param string $externalNetworkId
     * @param integer $blockId
     */
    public function checkAction(Request $request, $externalNetworkId, $blockId)
    {
        $this->isGranted('BUSINESS_MANAGE_LINE_TIMETABLE');

        $this->isPostAjax($request);

        $perimeter = $this->get('nmm.perimeter_manager')->findOneByExternalNetworkId(
            $this->getUser()->getCustomer(),
            $externalNetworkId
        );

        $block = $this->get('canal_tp_mtt.block_manager')->findBlock($blockId);
        $timetable = $block->getLineTimetable();

        // checking that the provided block is a calendar with data and is accessible via the network
        if (empty($timetable)) {
            return $this->prepareJsonResponse(
                $this->get('translator')->trans(
                    'line_timetable.error.block_not_linked',
                    array('%blockId%' => $blockId)
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
        if ($block->getContent() == null) {
            return $this->prepareJsonResponse(
                $this->get('translator')->trans(
                    'line_timetable.error.block_empty',
                    array('%blockId%' => $blockId)
                ),
                JsonResponse::HTTP_UNPROCESSABLE_ENTITY
            );
        }
        if ($block->getType() !== BlockRepository::CALENDAR_TYPE) {
            return $this->prepareJsonResponse(
                $this->get('translator')->trans(
                    'line_timetable.error.block_not_calendar',
                    array('%blockId%' => $blockId)
                ),
                JsonResponse::HTTP_UNPROCESSABLE_ENTITY
            );
        }

        $schedule = null;
        if ($timetable instanceof LineTimetable) {
            $parameters = array();
            $parameters['hourOffset'] = intVal($this->container->getParameter('canal_tp_mtt.hour_offset'));

            $selectedStopPoints = $block->getLineTimetable()->getSelectedStopPointsByRoute($block->getExternalRouteId());

            if (!$selectedStopPoints->isEmpty()) {
                $parameters['stopPoints'] = $selectedStopPoints;
            }

            $frequency = $request->query->get('frequency');

            $parameters['limits'] = $frequency['limits'];
            $parameters['checkFrequency'] = true;

            $schedule = $this->get('canal_tp_mtt.calendar_manager')->getCalendarForBlock(
                $perimeter->getExternalCoverageId(),
                $block,
                $parameters
            );

            return $this->render(
                'CanalTPMttBundle:Frequency:check.html.twig',
                array(
                    'schedule'  => $schedule,
                    'frequency' => (int)($frequency['time'])*60
                )
            );
        } else {
            return $this->prepareJsonResponse(
                '',
                JsonResponse::HTTP_UNPROCESSABLE_ENTITY
            );
        }
    }
}
