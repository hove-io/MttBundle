<?php

namespace CanalTP\MttBundle\Controller;

/*
 * CalendarController
 */
class CustomerController extends AbstractController
{
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
}
