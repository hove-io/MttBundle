<?php

namespace CanalTP\MttBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use CanalTP\MttBundle\Form\Type\NetworkType;

class NetworkController extends AbstractController
{
    private $networkManager = null;

    public function indexAction()
    {
        return $this->render(
            'CanalTPMttBundle:Network:index.html.twig'
        );
    }

    // TODO: Duplicate in CanalTPSamCoreBundle:Client (controller)
    public function byCoverageAction(Request $request, $externalCoverageId)
    {
        $response = new JsonResponse();
        $navitia = $this->get('sam_navitia');

        $navitia->setToken($request->query->get('token'));
        $networks = $navitia->getNetworks($externalCoverageId);

        $response->setData(
            array(
                'status' => Response::HTTP_OK,
                'networks' => $networks
            )
        );

        return ($response);
    }

    private function buildForm($networkId)
    {
        $coverage = $this->get('sam_navitia')->getCoverages();
        $layoutConfigs = $this->get('canal_tp_mtt.layout_config')->findAll();

        $form = $this->createForm(
            new NetworkType($coverage->regions, $layoutConfigs, $this->get('security.context')->isGranted('BUSINESS_ASSIGN_NETWORK_LAYOUT')),
            $this->networkManager->find($networkId),
            array(
                'action' => $this->generateUrl(
                    'canal_tp_mtt_network_edit',
                    array('networkId' => $networkId)
                )
            )
        );

        return ($form);
    }

    private function processForm(Request $request, $form, $networkId)
    {
        $form->handleRequest($request);

        if ($form->isValid()) {
            $this->networkManager->save($form->getData(), $networkId);

            return $this->redirect(
                $this->generateUrl('canal_tp_mtt_network_list')
            );
        }

        return (null);
    }

    public function editAction(Request $request, $networkId)
    {
        $this->networkManager = $this->get('canal_tp_mtt.network_manager');

        $form = $this->buildForm($networkId);
        $render = $this->processForm($request, $form, $networkId);
        if (!$render) {
            return $this->render(
                'CanalTPMttBundle:Network:form.html.twig',
                array('form' => $form->createView())
            );
        }

        return ($render);
    }

    public function listAction()
    {
        $this->networkManager = $this->get('canal_tp_mtt.network_manager');

        return $this->render(
            'CanalTPMttBundle:Network:list.html.twig',
            array(
                'pageTitle'=> 'menu.networks_manage',
                'no_left_menu' => true,
                'networks' => $this->networkManager->findAll()
            )
        );
    }
}
