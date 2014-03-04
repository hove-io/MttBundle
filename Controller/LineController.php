<?php

namespace CanalTP\MttBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use CanalTP\MttBundle\Entity\Line;

/*
 * LineController
 */
class LineController extends Controller
{
    /*
     * @function process a form to save a layout for a line. Insert a line in bdd if needed.
     */
    private function processForm($form, $line, $params)
    {
        if (empty($line)) {
            $data = $form->getData();
            $line = new Line();
            $line->setExternalId($params['line_id']);
            $line->setLayout($data['layout']);
        }
        if ($line->getLayout() != null) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($line);
            $em->flush();

            $this->get('session')->getFlashBag()->add(
                'notice',
                $this->get('translator')->trans('line.layout_chosen', array(), 'default')
            );
        }

        return $this->redirect($this->generateUrl('canal_tp_meth_stop_point_list', $params));
    }

    /*
     * @function display a form to choose a layout for a given line or save this form and redirects
     */
    public function chooseLayoutAction($coverage_id, $network_id, $line_id, $externalRouteId)
    {

        $params = array('coverage_id'    => $coverage_id,
                        'network_id'     => $network_id,
                        'line_id'        => $line_id,
                        'externalRouteId'=> $externalRouteId);
        $line = $this->getDoctrine()
                ->getRepository('CanalTPMttBundle:Line')
                ->findOneBy(
                    array('externalId' => $line_id)
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
                'CanalTPMttBundle:Line:chooseLayout.html.twig',
                array(
                    'form'        => $form->createView(),
                    'line_layout' => false
                )
            );
        }
    }
}
