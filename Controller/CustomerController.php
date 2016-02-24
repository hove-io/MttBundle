<?php

namespace CanalTP\MttBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use CanalTP\MttBundle\Form\Type\CustomerType;
use CanalTP\MttBundle\Entity\LayoutCustomer;

/*
 * CalendarController
 */
class CustomerController extends AbstractController
{
    public function listAction($externalNetworkId)
    {
        $this->isGranted('BUSINESS_ASSIGN_MODEL');
        $customerManager = $this->get('sam_core.customer');
        $customerApplications = $customerManager->findByCurrentApp();
        $layoutManager = $this->get('canal_tp_mtt.layout');
        $customers = array();

        foreach ($customerApplications as $customerApplication) {
            $customer = $customerManager->find($customerApplication->getCustomer());
            $layouts = $layoutManager->findByCustomer($customer);
            $customers[] = array(
                'customerEntity' => $customer,
                'layouts' => $layouts
            );
        }

        return $this->render(
            'CanalTPMttBundle:Customer:list.html.twig',
            array(
                'externalNetworkId' => $externalNetworkId,
                'customers' => $customers,
                'no_left_menu' => true
            )
        );
    }

    private function buildForm($customerId, $externalNetworkId)
    {
        $layouts = $this->get('canal_tp_mtt.layout')->findAll();
        $form = $this->createForm(
            new CustomerType($layouts, $customerId),
            new LayoutCustomer(),
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
        $this->isGranted('BUSINESS_ASSIGN_MODEL');
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
