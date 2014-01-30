<?php

namespace CanalTP\MethBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use CanalTP\MethBundle\Entity\Block;
use CanalTP\MethBundle\Entity\Line;

class BlockController extends Controller
{
    /*
     * @function returns form for a given block type or save content of the block using Form factory
     */
    public function editAction($line_id, $dom_id, $block_type = 'text', $stop_point = null)
    {
        $blockTypeFactory = $this->get('canal_tp_meth.form.factory.block');
        $data = array('dom_id' => $dom_id, 'type_id' => $block_type, 'stop_point' => $stop_point);
        $repo = $this->getDoctrine()->getRepository('CanalTPMethBundle:Block', 'meth');

        if (empty($stop_point))
        {
            $block = $repo->findByLineAndDomId($line_id, $dom_id);
        }
        else
        {
            $block = $repo->findByStopPointAndDomId($stop_point, $dom_id);
        }
        
        $blockTypeFactory->init($block_type, $data, $block);
        $form = $blockTypeFactory->buildForm()
            ->setAction($this->getRequest()->getRequestUri())
            ->setMethod('POST')->getForm();
        $form->handleRequest($this->getRequest());

        if ($form->isValid()) {
            $blockTypeFactory->buildHandler()->process($form->getData(), $line_id);

            return $this->redirect($this->generateUrl(
                    'canal_tp_meth_line_edit_layout',
                    array('line_id' => $line_id, 'stopPoint' => $stop_point)
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
