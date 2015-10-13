<?php

namespace CanalTP\MttBundle\Services;

use Doctrine\Common\Persistence\ObjectManager;

use CanalTP\MttBundle\Entity\LineTimetable;
use CanalTP\MttBundle\Entity\LineConfig;

/**
 * Class LineTimetableManager
 * @package CanalTP\MttBundle\Services
 */
class LineTimetableManager
{
    private $om = null;
    private $repository = null;

    /**
     * Constructor
     *
     * @param ObjectManager $om
     */
    public function __construct(ObjectManager $om)
    {
        $this->om = $om;
        $this->repository = $this->om->getRepository('CanalTPMttBundle:LineTimetable');
    }

    /**
     * Find or create LineTimetable
     *
     * @param $externalLineId
     * @param $perimeter
     * @param $lineConfig
     * @return LineTimetable
     */
    public function findOrCreateLineTimetable(LineConfig $lineConfig)
    {
        $lineTimetable = $this->repository->findOneBy(
            array(
                'lineConfig' => $lineConfig
            )
        );

        if (empty($lineTimetable)) {
            $lineTimetable = new LineTimetable();
            $lineTimetable->setLineConfig($lineConfig);
            $this->om->persist($lineTimetable);
            $this->om->flush();
        }

        return $lineTimetable;
    }
}
