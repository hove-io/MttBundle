<?php

namespace CanalTP\MttBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use CanalTP\MttBundle\Form\Type\CustomerType;
use CanalTP\MttBundle\Entity\LayoutConfigCustomer;

/*
 * CalendarController
 */
class CustomerController extends AbstractController
{
    private $networkManager = null;

    public function listAction($externalNetworkId)
    {
        $this->isGranted('BUSINESS_MANAGE_CUSTOMER');
        $customerManager = $this->get('sam_core.customer');
        $customerApplications = $customerManager->findByCurrentApp();
        $customers = array();

        foreach ($customerApplications as $customerApplication) {
            $customer = $customerApplication->getCustomer();
            // TODO: Call "$customerManager->getPerimetrers($customer)" to set customer perimeters
            // $customer->setPerimeters($customerManager->getPerimetrers($customer));
            $customers[] = $customer;
        }

        return $this->render(
            'CanalTPMttBundle:Customer:list.html.twig',
            array(
                'externalNetworkId' => $externalNetworkId,
                'customers' => $customers
            )
        );
    }

    private function buildForm($customerId, $externalNetworkId)
    {
        $layoutConfigs = $this->get('canal_tp_mtt.layout_config')->findAll();
        $form = $this->createForm(
            new CustomerType($layoutConfigs, $customerId),
            new LayoutConfigCustomer(),
            array(
                'em' => $this->getDoctrine()->getManager(),
                'action' => $this->generateUrl(
                    'canal_tp_mtt_customer_assign_layout',
                    array(
                        'customerId' => $customerId,
                        'externalNetworkId' => $externalNetworkId
                    )
                )
            )
        );

        return ($form);
    }

    private function processForm(Request $request, $form, $customerId, $externalNetworkId)
    {
        $form->handleRequest($request);

        if ($form->isValid()) {

            return $this->redirect(
                $this->generateUrl('canal_tp_mtt_customer_list', array(
                    'externalNetworkId' => $externalNetworkId
                ))
            );
        }

        return (null);
    }

    public function assignLayoutAction(Request $request, $externalNetworkId, $customerId)
    {
        $this->networkManager = $this->get('canal_tp_mtt.network_manager');

        $form = $this->buildForm($customerId, $externalNetworkId);
        $render = $this->processForm($request, $form, $customerId, $externalNetworkId);

        if (!$render) {
            return $this->render(
                'CanalTPMttBundle:Customer:assignLayoutForm.html.twig',
                array('form' => $form->createView())
            );
        }
        return ($render);
    }
}
