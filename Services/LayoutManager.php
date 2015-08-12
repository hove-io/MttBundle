<?php

namespace CanalTP\MttBundle\Services;

use Doctrine\Common\Persistence\ObjectManager;
use CanalTP\NmmPortalBundle\Entity\Customer;
use CanalTP\SamEcoreUserManagerBundle\Entity\User;

class LayoutManager
{
    private $om = null;
    private $repository = null;

    public function __construct(ObjectManager $om)
    {
        $this->om = $om;
        $this->repository = $this->om->getRepository('CanalTPMttBundle:Layout');
    }

    public function findAll()
    {
        return ($this->repository->findAll());
    }

    public function findByCustomer(Customer $customer)
    {
        return $this->repository->findLayoutByCustomer($customer);
    }
}
