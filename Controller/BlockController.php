<?php

namespace CanalTP\MethBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class BlockController extends Controller
{
    /*
     * @function display a form to choose a layout for a given line or save this form and redirects
     */
    public function getFormAction($block_type = 'text', $dom_id)
    {
        $defaultData = array();
        $form = $this->createFormBuilder($defaultData)
            ->add('title', 'text')
            ->add('content', 'textarea', array('attr' => array('rows' => 5)))
            ->add('_dom_id', 'hidden', array('data' => $dom_id))
            ->setAction($this->getRequest()->getRequestUri())
            ->setMethod('POST')
            ->getForm();

        $form->handleRequest($this->getRequest());

        if ($form->isValid()) {
            //TODO save data
            $data = $form->getData();
        } else {
            return $this->render(
                'CanalTPMethBundle:Block:get_form.html.twig',
                array(
                    'form'        => $form->createView(),
                )
            );
        }
    }
}
