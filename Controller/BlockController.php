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
        $block = $this->getDoctrine()->getRepository('CanalTPMethBundle:Block', 'meth')->findByLineAndDomId($line_id, $dom_id);
        // var_dump($block[0]->getDomId());die;
        $form = $this->createFormBuilder($block)
            ->add('title', 'text')
            ->add('content', 'textarea', array('attr' => array('rows' => 5)))
            ->add('dom_id', 'hidden', array('data' => $dom_id))
            ->add('type_id', 'hidden', array('data' => $block_type))
            ->setAction($this->getRequest()->getRequestUri())
            ->setMethod('POST')
            ->getForm();

        $form->handleRequest($this->getRequest());

        if ($form->isValid()) {
            return $this->processForm($form, $block, $line_id);
        } else {
            return $this->render(
                'CanalTPMethBundle:Block:get_form.html.twig',
                array(
                    'form'        => $form->createView(),
                )
            );
        }
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
