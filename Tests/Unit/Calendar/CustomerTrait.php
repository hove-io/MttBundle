<?php

namespace CanalTP\MttBundle\Tests\Unit\Calendar;

use CanalTP\NmmPortalBundle\Entity\Customer;
use CanalTP\NmmPortalBundle\Entity\NavitiaEntity;
use CanalTP\NmmPortalBundle\Entity\Perimeter;

trait CustomerTrait
{
    public function makeCustomer($identifier, array $networks)
    {
        $customer = new Customer();
        $customer->setIdentifier($identifier);
        $customer->setNavitiaEntity(new NavitiaEntity());

        foreach ($networks as $network) {
            $perimeter = new Perimeter();
            $perimeter->setExternalNetworkId($network);
            $customer->getNavitiaEntity()->addPerimeter($perimeter);
        }

        return $customer;
    }
}
