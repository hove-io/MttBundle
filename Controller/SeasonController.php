<?php

namespace CanalTP\MttBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use CanalTP\MttBundle\Form\Type\SeasonType;
use CanalTP\MttBundle\Entity\Season;

class SeasonController extends AbstractController
{
    private $seasonManager = null;

    private function buildForm($networkId, $seasonId)
    {
        $form = $this->createForm(
            new SeasonType(
                $this->seasonManager->findAllByNetworkId($networkId),
                $seasonId
            ),
            $this->seasonManager->getSeasonWithNetworkIdAndSeasonId($networkId, $seasonId),
            array(
                'action' => $this->generateUrl(
                    'canal_tp_mtt_season_edit',
                    array(
                        'network_id' => $networkId,
                        'season_id' => $seasonId
                    )
                )
            )
        );

        return ($form);
    }

    private function processForm(Request $request, $form, $networkId)
    {
        $form->handleRequest($request);

        if ($form->isValid()) {
            $this->seasonManager->save($form->getData());
            $seasonToClone = $form->getData()->getSeasonToClone();
            if (!empty($seasonToClone)) {
                $seasonCopier = $this->get('canal_tp_mtt.season_copier');
                $seasonCopier->run($seasonToClone, $form->getData());
            }

            return $this->redirect(
                $this->generateUrl(
                    'canal_tp_mtt_season_list',
                    array(
                        'network_id' => $networkId,
                    )
                )
            );
        }

        return (null);
    }

    public function editAction(Request $request, $network_id, $season_id)
    {
        $this->isGranted('BUSINESS_MANAGE_SEASON');
        $this->seasonManager = $this->get('canal_tp_mtt.season_manager');

        $form = $this->buildForm($network_id, $season_id);
        $render = $this->processForm($request, $form, $network_id);
        if (!$render) {
            return $this->render(
                'CanalTPMttBundle:Season:form.html.twig',
                array('form' => $form->createView())
            );
        }

        return ($render);
    }

    public function deleteAction($networkId, $seasonId)
    {
        $this->isGranted('BUSINESS_MANAGE_SEASON');

        $seasonManager = $this->get('canal_tp_mtt.season_manager');
        $season = $seasonManager->find($seasonId);
        $this->get('canal_tp_mtt.media_manager')->deleteSeasonMedias($season);
        $seasonManager->remove($season);
        return $this->redirect(
            $this->generateUrl(
                'canal_tp_mtt_season_list',
                array(
                    'network_id' => $networkId,
                )
            )
        );
    }
    
    public function listAction(Request $request, $network_id)
    {
        $this->isGranted('BUSINESS_MANAGE_SEASON');
        $this->seasonManager = $this->get('canal_tp_mtt.season_manager');

        return $this->render(
            'CanalTPMttBundle:Season:list.html.twig',
            array(
                'no_left_menu' => true,
                'networkId' => $network_id,
                'seasons' => $this->seasonManager->findAllByNetworkId($network_id)
            )
        );
    }
}
