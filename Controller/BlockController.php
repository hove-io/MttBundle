<?php

namespace CanalTP\MethBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use CanalTP\MethBundle\Entity\Block;

class BlockController extends Controller
{
    /*
     * @function returns form for a given block type or save content of the block using Form factory
     */
    public function editAction($externalCoverageId, $dom_id, $timetableId, $block_type = 'text', $stop_point = null)
    {
        $blockTypeFactory = $this->get('canal_tp_meth.form.factory.block');
        $data = array('dom_id' => $dom_id, 'type_id' => $block_type, 'stop_point' => $stop_point);
        $timetable = $this->getDoctrine()->getRepository('CanalTPMethBundle:Timetable', 'mtt')->find($timetableId);
        $repo = $this->getDoctrine()->getRepository('CanalTPMethBundle:Block', 'mtt');

        if (empty($stop_point)) {
            $block = $repo->findByTimetableAndDomId($timetableId, $dom_id);
        } else {
            $block = $repo->findByStopPointAndDomId($stop_point, $dom_id);
        }

        $blockTypeFactory->init($block_type, $data, $block, $externalCoverageId);
        $form = $blockTypeFactory->buildForm()
            ->setAction($this->getRequest()->getRequestUri())
            ->setMethod('POST')->getForm();
        $form->handleRequest($this->getRequest());

        if ($form->isValid()) {

            $blockTypeFactory->buildHandler()->process($form->getData(), $timetable);
            // var_dump($timetable->getExternalRouteId());die;
            return $this->redirect(
                $this->generateUrl(
                    'canal_tp_meth_timetable_edit',
                    array(
                        'externalCoverageId'    => $externalCoverageId,
                        'externalRouteId'       => $timetable->getExternalRouteId(),
                        'externalStopPointId'   => $stop_point
                    )
                )
            );
        }

        return $this->render(
            'CanalTPMethBundle:Block:get_form.html.twig',
            array(
                'form'        => $form->createView(),
            )
        );
    }
}
