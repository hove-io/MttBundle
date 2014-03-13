<?php

namespace CanalTP\MttBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use CanalTP\MttBundle\Form\Type\SeasonType;
use CanalTP\MttBundle\Entity\Season;

class SeasonController extends Controller
{
    private $seasonManager = null;

    public function buildForm($coverageId, $networkId, $seasonId)
    {
        $form = $this->createForm(
            new SeasonType(),
            $this->seasonManager->getSeasonWithNetworkIdAndSeasonId($networkId, $seasonId),
            array(
                'action' => $this->generateUrl(
                    'canal_tp_mtt_season_edit',
                    array(
                        'coverage_id' => $coverageId,
                        'network_id' => $networkId
                    )
                )
            )
        );
        return ($form);
    }

    private function processForm(Request $request, $form)
    {
        $form->handleRequest($request);

        if ($form->isValid()) {
            $this->seasonManager->save($form->getData());
            return $this->redirect($this->generateUrl('canal_tp_meth_homepage'));
        }
        return (null);
    }

    public function editAction(Request $request, $coverage_id, $network_id, $season_id)
    {
        $this->seasonManager = $this->get('canal_tp_mtt.season_manager');

        $form = $this->buildForm($coverage_id, $network_id, $season_id);
        $render = $this->processForm($request, $form);
        if (!$render) {
            return $this->render(
                'CanalTPMttBundle:Season:form.html.twig',
                array('form' => $form->createView())
            );
        }
        return ($render);
    }

    public function listAction(Request $request, $network_id, $coverage_id)
    {
        $this->seasonManager = $this->get('canal_tp_mtt.season_manager');

        return $this->render(
            'CanalTPMttBundle:Season:list.html.twig',
            array(
                'no_left_menu' => true,
                'coverageId' => $coverage_id,
                'networkId' => $network_id,
                'seasons' => $this->seasonManager->findAllByNetworkId($network_id)
            )
        );
    }
}
