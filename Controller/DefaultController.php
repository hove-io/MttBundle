<?php

namespace CanalTP\MethBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction()
    {
        $networks = $this->getDoctrine()
        ->getRepository('CanalTPMethBundle:Network', 'meth')
        ->findNetworksByUserId($this->getUser()->getId());

        return $this->render('CanalTPMethBundle:Default:index.html.twig', array('networks' => $networks));
    }
    
    public function navigationAction()
    {
        $meth_navitia = $this->get('canal_tp_meth.navitia');
        $networks = $this->getDoctrine()
        ->getRepository('CanalTPMethBundle:Network', 'meth')
        ->findNetworksByUserId($this->getUser()->getId());

         // Configuration
        $result = $meth_navitia->getLinesByMode($networks[0]['coverage_id'], $networks[0]['name_id']);
        // var_dump($result);die;
        // var_dump($result);die;
        return $this->render(
            'CanalTPMethBundle:Default:navigation.html.twig',
            array('result' => $result)
        );
    }
}
