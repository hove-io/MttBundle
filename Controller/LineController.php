<?php

namespace CanalTP\MethBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

use CanalTP\MethBundle\Form\Type\LayoutType;
use CanalTP\MethBundle\Entity\Line;

class LineController extends Controller
{
    /*
     * @function display a form to choose a layout for a given line or save this form and redirects
     */
    public function chooseLayoutAction($coverage_id, $network_id, $line_id, $route_id)
    {
        $line = $this->getDoctrine()->getRepository('CanalTPMethBundle:Line', 'meth')->findOneBy(array(
            'coverageId'   => $coverage_id,
            'networkId'    => $network_id,
            'navitiaLineId'=> $line_id,
        ));
        $url_params = array(
            'coverage_id'   => $coverage_id,
            'network_id'    => $network_id,
            'line_id'=> $line_id,
            'route_id'=> $route_id,
        );
        $form = $this->createFormBuilder($line)
            ->add('layout', 'layout', array(
                'empty_value' => 'Choose a layout',
            ))
            ->setAction($this->generateUrl('canal_tp_meth_choose_layout', $url_params))
            ->setMethod('POST')
            ->getForm();
        $form->handleRequest($this->getRequest());
        if ($form->isValid())
        {
            $data = $form->getData();
            if (empty($line))
            {
                $line = new Line();
                $line->setCoverageId($coverage_id);
                $line->setNetworkId($network_id);
                $line->setNavitiaLineId($line_id);
                $line->setLayout($data['layout']);
            }
            $em = $this->getDoctrine()->getManager('meth');
            $em->persist($line);
            $em->flush();
            
            $this->get('session')->getFlashBag()->add('notice', 'line.flash.layout_chosen');
            return $this->redirect($this->generateUrl('canal_tp_meth_stop_point_list', $url_params));
        }
        else
        {
            return $this->render(
                'CanalTPMethBundle:Line:chooseLayout.html.twig',
                array(
                    'form'        => $form->createView(),
                    'line_layout' => false
                )
            );
        }
    }
}
