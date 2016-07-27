<?php

namespace CanalTP\MttBundle\Entity;

use Doctrine\ORM\EntityRepository;

class CalendarRepository extends EntityRepository
{
    /**
     * Get an calendars id by external coverage id
     *
     * @param string $externalCoverageId External coverage id
     * @param string $applicationCanonicalName Application canonical Name
     *
     * @return array Array of calendars by external coverage id
     */
    public function findCalendarByExternalCoverageIdAndApplication($externalCoverageId, $applicationCanonicalName)
    {
        $qb = $this->createQueryBuilder('cal');
        $qb
            ->addSelect('cus')
            ->addSelect('ne')
            ->addSelect('per')
            ->leftJoin('cal.customer', 'cus')
            ->leftJoin('cus.navitiaEntity', 'ne')
            ->join('ne.perimeters', 'per', 'WITH', 'per.externalCoverageId=:externalCoverageId')
            ->leftJoin('cus.applications', 'ca', 'WITH', 'ca.isActive=True')
            ->leftJoin('ca.application', 'app')
            ->andWhere('app.canonicalName=:applicationCanonicalName')
            ->setParameter('externalCoverageId', $externalCoverageId)
            ->setParameter('applicationCanonicalName', $applicationCanonicalName)
        ;

        return $qb->getQuery()->getResult();
    }
}
