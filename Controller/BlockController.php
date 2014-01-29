<?php

namespace CanalTP\MethBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Serializer\Serializer;

use CanalTP\MethBundle\Entity\Block;
use CanalTP\MethBundle\Entity\Line;

class BlockController extends Controller
{
    /*
     * @function display a form to choose a layout for a given line or save this form and redirects
     */
    public function editAction($line_id, $dom_id, $block_type = 'text')
    {

        $blockTypeFactory = $this->get('canal_tp_meth.form.factory.block');

        $blockTypeFactory->init($block_type);
        $form = $blockTypeFactory->buildForm()->getForm();
        $handler = $blockTypeFactory->buildHandler();

        return $this->render(
            'CanalTPMethBundle:Block:get_form.html.twig',
            array(
                'form'        => $form->createView(),
            )
        );
    }

    private function processForm($form, $block, $line_id)
    {
        if (empty($block)) {
            $data = $form->getData();
            $block = new Block();
            // get partialreference to avoid SQL statement
            $line = $this->getDoctrine()->getEntityManager('meth')->getPartialReference('CanalTP\MethBundle\Entity\Line', $line_id);
            $block->setLine($line);
            $block->setContent($data['content']);
            $block->setTitle($data['title']);
            $block->setDomId($data['dom_id']);
            $block->setTypeId($data['dom_id']);
        }

        $em = $this->getDoctrine()->getManager('meth');
        $em->persist($block);
        $em->flush();

        return $this->redirect($this->generateUrl(
            'canal_tp_meth_line_edit_layout',
            array(
                    'line_id' => $line_id,
                )
            )
        );
    }
}
