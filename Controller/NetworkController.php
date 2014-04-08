<?php

namespace CanalTP\MttBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use CanalTP\MttBundle\Form\Type\NetworkType;
use CanalTP\MttBundle\Entity\Network;

class NetworkController extends AbstractController
{
    private $networkManager = null;

    public function indexAction()
    {
        return $this->render(
            'CanalTPMttBundle:Network:index.html.twig'
        );
    }

    private function buildForm($networkId)
    {
        $coverage = $this->get('canal_tp_mtt.navitia')->getCoverages();

        $form = $this->createForm(
            new NetworkType($coverage->regions, $networkId),
            $this->networkManager->find($networkId),
            array(
                'action' => $this->generateUrl(
                    'canal_tp_mtt_network_edit'
                )
            )
        );
        return ($form);
    }

    private function processForm(Request $request, $form)
    {
        $form->handleRequest($request);

        if ($form->isValid()) {
            $this->networkManager->save($form->getData());
            return $this->redirect(
                $this->generateUrl('canal_tp_mtt_network_list')
            );
        }
        return (null);
    }

    public function editAction(Request $request, $network_id)
    {
        $this->networkManager = $this->get('canal_tp_mtt.network_manager');

        $form = $this->buildForm($network_id);
        $render = $this->processForm($request, $form);
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
                'no_left_menu' => true,
                'networks' => $this->networkManager->findAll()
            )
        );
    }
}
