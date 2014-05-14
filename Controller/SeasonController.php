<?php

namespace CanalTP\MttBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use CanalTP\MttBundle\Form\Type\SeasonType;
use CanalTP\MttBundle\Form\Type\SeasonPublicationType;
use CanalTP\MttBundle\Entity\Season;

class SeasonController extends AbstractController
{
    private $seasonManager = null;

    private function buildForm($externalNetworkId, $seasonId)
    {
        $form = $this->createForm(
            new SeasonType(
                $this->seasonManager->findAllByNetworkId($externalNetworkId),
                $seasonId
            ),
            $this->seasonManager->getSeasonWithNetworkIdAndSeasonId($externalNetworkId, $seasonId),
            array(
                'action' => $this->generateUrl(
                    'canal_tp_mtt_season_edit',
                    array(
                        'externalNetworkId' => $externalNetworkId,
                        'season_id' => $seasonId
                    )
                )
            )
        );

        return ($form);
    }

    private function processForm(Request $request, $form, $externalNetworkId)
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
                        'externalNetworkId' => $externalNetworkId,
                    )
                )
            );
        }

        return (null);
    }

    public function generatePdfAction($externalNetworkId, $seasonId, $publishOnComplete = false)
    {
        $seasonManager = $this->get('canal_tp_mtt.season_manager');
        $pdfPayloadGenerator = $this->get('canal_tp_mtt.season_pdf_payload_generator');
        $amqpPdfGenPublisher = $this->get('canal_tp_mtt.amqp_pdf_gen_publisher');
        
        $season = $seasonManager->find($seasonId);
        $payloads = $pdfPayloadGenerator->generate($season);
        $amqpPdfGenPublisher->publish($payloads, $season, array('publishSeasonOnComplete' => $publishOnComplete));
        
        $this->get('session')->getFlashBag()->add(
            'success',
            $this->get('translator')->trans(
                'season.pdf_generation_task_has_started',
                array(
                    '%count_jobs%' => count($payloads)
                ),
                'default'
            )
        );
        return $this->redirect(
            $this->generateUrl(
                'canal_tp_mtt_season_list',
                array(
                    'externalNetworkId' => $externalNetworkId,
                )
            )
        );
    }

    public function editAction(Request $request, $externalNetworkId, $season_id)
    {
        $this->isGranted('BUSINESS_MANAGE_SEASON');
        $this->seasonManager = $this->get('canal_tp_mtt.season_manager');

        $form = $this->buildForm($externalNetworkId, $season_id);
        $render = $this->processForm($request, $form, $externalNetworkId);
        if (!$render) {
            return $this->render(
                'CanalTPMttBundle:Season:form.html.twig',
                array('form' => $form->createView())
            );
        }

        return ($render);
    }

    public function deleteAction($externalNetworkId, $seasonId)
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
                    'externalNetworkId' => $externalNetworkId,
                )
            )
        );
    }

    public function publishAction($externalNetworkId, $seasonId)
    {
        $this->isGranted('BUSINESS_MANAGE_SEASON');
        $withGeneration = $this->getRequest()->get('withGeneration', false);
        if ($withGeneration == 1) {
            $this->generatePdfAction($externalNetworkId, $seasonId, true);
        } else {
            $this->get('canal_tp_mtt.season_manager')->publish($seasonId);
        }
        return $this->redirect(
            $this->generateUrl(
                'canal_tp_mtt_season_list',
                array(
                    'externalNetworkId' => $externalNetworkId,
                )
            )
        );
    }

    public function unpublishAction($externalNetworkId, $seasonId)
    {
        $this->isGranted('BUSINESS_MANAGE_SEASON');
        $this->get('canal_tp_mtt.season_manager')->unpublish($seasonId);

        return $this->redirect(
            $this->generateUrl(
                'canal_tp_mtt_season_list',
                array(
                    'externalNetworkId' => $externalNetworkId,
                )
            )
        );
    }

    public function listAction(Request $request, $externalNetworkId)
    {
        $this->isGranted('BUSINESS_MANAGE_SEASON');
        $this->seasonManager = $this->get('canal_tp_mtt.season_manager');

        return $this->render(
            'CanalTPMttBundle:Season:list.html.twig',
            array(
                'no_left_menu' => true,
                'externalNetworkId' => $externalNetworkId,
                'seasons' => $this->seasonManager->findAllByNetworkId($externalNetworkId)
            )
        );
    }
}
