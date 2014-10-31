<?php

namespace CanalTP\MttBundle\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\Collections\ArrayCollection;
use CanalTP\MttBundle\Entity\LayoutConfigCustomer;

class LayoutConfigCustomerTransformer implements DataTransformerInterface
{
    private $om = null;
    private $repository = null;
    private $customerId = null;

    public function __construct(ObjectManager $om, $customerId)
    {
        $this->om = $om;
        $this->repository = $om->getRepository('CanalTPMttBundle:LayoutConfigCustomer');
        $this->customerId = $customerId;
    }

    public function transform($layoutConfigCustomer)
    {
        if ($layoutConfigCustomer === null) {
            return ($layoutConfigCustomer);
        }
        $layoutConfigsCustomers = $this->repository->findBy(array('customer' => $this->customerId));
        foreach ($layoutConfigsCustomers as $layoutConfigsCustomer) {
            $layoutConfigCustomer->addLayoutConfigAssigned($layoutConfigsCustomer->getLayoutConfig());
        }

        return $layoutConfigCustomer;
    }

    private function deleteAllLayoutConfigsCustomer()
    {
        $layoutConfigsCustomer = $this->repository->findBy(array('customer' => $this->customerId));

        foreach ($layoutConfigsCustomer as $layoutConfigCustomer) {
            $this->om->remove($layoutConfigCustomer);
        }
    }

    public function reverseTransform($layoutConfigCustomer)
    {
        if (!$layoutConfigCustomer) {
            return $layoutConfigCustomer;
        }
        $customerRepo = $this->om->getRepository('CanalTPNmmPortalBundle:Customer');
        $customer = $customerRepo->find($this->customerId);

        $this->deleteAllLayoutConfigsCustomer();
        foreach ($layoutConfigCustomer->getLayoutConfigsAssigned() as $layoutConfigSelected) {
            $currentLayoutConfigCustomer = new LayoutConfigCustomer();
            $currentLayoutConfigCustomer->setCustomer($customer);
            $currentLayoutConfigCustomer->setLayoutConfig($layoutConfigSelected);
            $this->om->persist($currentLayoutConfigCustomer);
        }

        $this->om->flush();

        return $layoutConfigCustomer;
    }
}
