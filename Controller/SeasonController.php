<?php

namespace CanalTP\MttBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use CanalTP\MttBundle\Form\Type\SeasonType;
use CanalTP\MttBundle\Entity\Season;

class SeasonController extends AbstractController
{
    private $seasonManager = null;

    private function buildForm($externalNetworkId, $seasonId)
    {
        $perimeterManager = $this->get('nmm.perimeter_manager');
        $perimeter = $perimeterManager->findOneByExternalNetworkId(
            $this->getUser()->getCustomer(),
            $externalNetworkId
        );
        $form = $this->createForm(
            new SeasonType(
                $this->seasonManager->findByPerimeter($perimeter),
                $seasonId
            ),
            $this->seasonManager->getSeasonWithPerimeterAndSeasonId($perimeter, $seasonId),
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
        $pdfPayloadGenerator = $this->get('canal_tp_mtt.pdf_payload_generator');
        $amqpPdfGenPublisher = $this->get('canal_tp_mtt.amqp_pdf_gen_publisher');

        $season = $seasonManager->find($seasonId);
        if ($this->addFlashIfSeasonLocked($season) == false) {
            try {
                $payloads = $pdfPayloadGenerator->getSeasonPayloads($season);
                $amqpPdfGenPublisher->publishSeasonPdfGen(
                    $payloads,
                    $season,
                    array('publishSeasonOnComplete' => $publishOnComplete)
                );
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
            } catch (\Exception $e) {
                $this->get('session')->getFlashBag()->add(
                    'danger',
                    $this->get('translator')->trans(
                        $e->getMessage(),
                        array(),
                        'exceptions'
                    )
                );
            }
        }

        return $this->redirect(
            $this->generateUrl(
                'canal_tp_mtt_homepage',
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
                array(
                    'form' => $form->createView(),
                    'title' => ($season_id ? 'season.edit' : 'season.create')
                )
            );
        }

        return ($render);
    }

    public function deleteAction($externalNetworkId, $seasonId)
    {
        $this->isGranted('BUSINESS_MANAGE_SEASON');

        $seasonManager = $this->get('canal_tp_mtt.season_manager');
        $season = $seasonManager->find($seasonId);
        if ($this->addFlashIfSeasonLocked($season) == false) {
            $this->get('canal_tp_mtt.media_manager')->deleteSeasonMedias($season);
            $seasonManager->remove($season);
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

    public function askPublishAction($externalNetworkId, $seasonId)
    {
        $this->isGranted('BUSINESS_MANAGE_SEASON');
        $season = $this->get('canal_tp_mtt.season_manager')->find($seasonId);

        return $this->render(
            'CanalTPMttBundle:Season:askPublication.html.twig',
            array(
                'externalNetworkId' => $externalNetworkId,
                'seasonId' => $seasonId
            )
        );
    }

    public function publishAction($externalNetworkId, $seasonId)
    {
        $this->isGranted('BUSINESS_MANAGE_SEASON');
        $withGeneration = $this->getRequest()->get('withGeneration', false);
        $season = $this->get('canal_tp_mtt.season_manager')->find($seasonId);
        if ($this->addFlashIfSeasonLocked($season) == false) {
            if ($withGeneration == 1) {
                $this->generatePdfAction($externalNetworkId, $seasonId, true);
            } else {
                $this->get('canal_tp_mtt.season_manager')->publish($seasonId);
            }
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
        $seasonManager = $this->get('canal_tp_mtt.season_manager');
        $season = $seasonManager->find($seasonId);
        if ($this->addFlashIfSeasonLocked($season) == false) {
            $seasonManager->unpublish($seasonId);
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

    public function listAction(Request $request, $externalNetworkId)
    {
        $this->isGranted('BUSINESS_MANAGE_SEASON');
        $seasonManager = $this->get('canal_tp_mtt.season_manager');
        $perimeterManager = $this->get('nmm.perimeter_manager');
        $perimeter = $perimeterManager->findOneByExternalNetworkId(
            $this->getUser()->getCustomer(),
            $externalNetworkId
        );

        return $this->render(
            'CanalTPMttBundle:Season:list.html.twig',
            array(
                'pageTitle'=> 'menu.seasons_manage',
                'externalNetworkId' => $externalNetworkId,
                'seasons' => $seasonManager->findByPerimeter($perimeter)
            )
        );
    }
}
