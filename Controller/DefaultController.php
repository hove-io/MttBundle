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
}
