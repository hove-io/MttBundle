<?php

namespace CanalTP\MttBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use CanalTP\MttBundle\Form\Type\SeasonType;
use CanalTP\MttBundle\Entity\Season;

class SeasonController extends Controller
{
    public function createAction($coverage_id, $network_id)
    {
        $season = new Season();
        $form = $this->createForm(
            new SeasonType(),
            $season,
            array(
                'action' => $this->generateUrl(
                    'canal_tp_meth_homepage'
                )
            )
        );

        return $this->render(
            'CanalTPMttBundle:Season:form.html.twig',
            array(
                'form'     => $form->createView()
            )
        );
    }
}
