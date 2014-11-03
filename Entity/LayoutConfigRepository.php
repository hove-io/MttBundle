<?php

namespace CanalTP\MttBundle\Entity;

use Doctrine\ORM\EntityRepository;
use CanalTP\NmmPortalBundle\Entity\Customer;

/**
 * LayoutConfigRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class LayoutConfigRepository extends EntityRepository
{
    // TODO: Add Translator service.
    public function orientationName($orientationType)
    {
        $name = 'Unknown';

        switch ($orientationType) {
            case Layout::ORIENTATION_LANDSCAPE:
                $name = 'Landscape';
                break;
        }
        return ($name);
    }

    public function findLayoutConfigByCustomer(Customer $customer)
    {
        $query = $this->getEntityManager()->createQueryBuilder()
            ->select('lc', 'c')
            ->from('CanalTPMttBundle:LayoutConfig', 'lc')
            ->join('lc.customers', 'c')
            ->where('c.customer = :customer')
            ->setParameter('customer', $customer)
            ->getQuery();

        return $query->getResult();
    }
}
