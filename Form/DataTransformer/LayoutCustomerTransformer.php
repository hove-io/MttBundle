<?php

namespace CanalTP\MttBundle\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;
use Doctrine\Common\Persistence\ObjectManager;
use CanalTP\MttBundle\Entity\LayoutCustomer;

class LayoutCustomerTransformer implements DataTransformerInterface
{
    private $om = null;
    private $repository = null;
    private $customerId = null;

    public function __construct(ObjectManager $om, $customerId)
    {
        $this->om = $om;
        $this->repository = $om->getRepository('CanalTPMttBundle:LayoutCustomer');
        $this->customerId = $customerId;
    }

    public function transform($layoutCustomer)
    {
        if ($layoutCustomer === null) {
            return ($layoutCustomer);
        }
        $layoutsCustomers = $this->repository->findBy(array('customer' => $this->customerId));

        foreach ($layoutsCustomers as $layoutsCustomer) {
            $layoutCustomer->addLayoutAssigned($layoutsCustomer->getLayout());
        }

        return $layoutCustomer;
    }

    private function deleteAllLayoutsCustomer()
    {
        $layoutsCustomer = $this->repository->findBy(array('customer' => $this->customerId));

        foreach ($layoutsCustomer as $layoutCustomer) {
            $this->om->remove($layoutCustomer);
        }
    }

    public function reverseTransform($layoutCustomer)
    {
        if (!$layoutCustomer) {
            return $layoutCustomer;
        }
        $customerRepo = $this->om->getRepository('CanalTPNmmPortalBundle:Customer');
        $customer = $customerRepo->find($this->customerId);

        $this->deleteAllLayoutsCustomer();
        foreach ($layoutCustomer->getLayoutsAssigned() as $layoutSelected) {
            $currentLayout = new LayoutCustomer();
            $currentLayout->setCustomer($customer);
            $currentLayout->setLayout($layoutSelected);
            $this->om->persist($currentLayout);
        }

        $this->om->flush();

        return $layoutCustomer;
    }
}
