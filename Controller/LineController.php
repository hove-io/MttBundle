<?php

namespace CanalTP\MethBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use CanalTP\MethBundle\Entity\Line;

class LineController extends Controller
{
    private function processForm($form, $line, $params)
    {
        if (empty($line)) {
            $data = $form->getData();
            $line = new Line();
            $line->setCoverageId($params['coverage_id']);
            $line->setNetworkId($params['network_id']);
            $line->setNavitiaId($params['line_id']);
            $line->setLayout($data['layout']);
        }
        if ($line->getLayout() != null) {
            $em = $this->getDoctrine()->getManager('mtt');
            $em->persist($line);
            $em->flush();

            $this->get('session')->getFlashBag()->add('notice', 'line.flash.layout_chosen');
        }

        return $this->redirect($this->generateUrl('canal_tp_meth_stop_point_list', $params));
    }

    /*
     * @function display a form to choose a layout for a given line or save this form and redirects
     */
    public function chooseLayoutAction($coverage_id, $network_id, $line_id, $route_id)
    {

        $params = array('coverage_id'   => $coverage_id,
                        'network_id'    => $network_id,
                        'line_id'       => $line_id,
                        'route_id'      => $route_id);
        $line = $this->getDoctrine()
                ->getRepository('CanalTPMethBundle:Line', 'mtt')
                ->findOneBy(array(
                    'coverageId'   => $coverage_id,
                    'networkId'    => $network_id,
                    'navitiaId'=> $line_id
                    )
                );

        $form = $this->createFormBuilder($line)
            ->add(
                'layout',
                'layout',
                array(
                    'empty_value' => 'Choose a layout',
                )
            )
            ->setAction($this->getRequest()->getRequestUri())
            ->setMethod('POST')
            ->getForm();

        $form->handleRequest($this->getRequest());
        if ($form->isValid()) {
            return ($this->processForm($form, $line, $params));
        } else {
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
