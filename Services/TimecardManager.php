<?php

namespace CanalTP\MttBundle\Services;

use Doctrine\Common\Persistence\ObjectManager;

use CanalTP\MttBundle\Entity\Timecard;
use Symfony\Component\Validator\Constraints\Time;

/**
 * Class TimecardManager
 * @package CanalTP\MttBundle\Services
 */
class TimecardManager
{
    private $om = null;
    private $repository = null;
    private $taskManager = null;
    private $perimeterManager = null;
    private $user = null;

    /**
     * @param ObjectManager $om
     * @param $perimeterManager
     */
    public function __construct(ObjectManager $om, $perimeterManager, $securityContext)
    {
        $this->om = $om;
        //$this->timecardPdfManager = $timecardPdfManager;
        $this->perimeterManager = $perimeterManager;
        $this->repository = $om->getRepository('CanalTPMttBundle:Timecard');
        $this->user = $securityContext->getToken()->getUser();
    }


    /**
     * @return array
     */
    public function findAll()
    {
        return ($this->repository->findAll());
    }

    /**
     * @param $perimeter
     * @param $timecardId
     * @return Timecard|null|object
     */
    public function getTimecardWithPerimeter($perimeter, $timecardId)
    {
        $timecard = $this->find($timecardId);

        if ($timecard == null) {
            $timecard = new Timecard();
            $timecard->setPerimeter($perimeter);
        }

        return ($timecard);
    }

    /**
     * @param $perimeter
     * @return mixed
     */
    public function findByPerimeter($perimeter)
    {
        return $this->repository->findByPerimeter($perimeter);
    }

    /**
     * @param $timecardId
     * @return null|object
     */
    public function find($timecardId)
    {
        return empty($timecardId) ? null : $this->repository->find($timecardId);
    }

    /**
     * @param $lineId
     * @param $routeId
     * @param $seasonId
     * @param $networkId
     * @return \CanalTP\MttBundle\Entity\Timecard|object
     */
    public function findByUniqueString($lineId,$routeId,$seasonId,$networkId)
    {
        $uniqueString = $networkId . '-' . $lineId . '-' . $routeId . '-' . $seasonId;

        /** @var  $perimeter \CanalTP\NmmPortalBundle\Entity\Perimeter */
        $perimeter = $this->perimeterManager->findOneByExternalNetworkId(
            $this->user->getCustomer(),
            $networkId
        );

        $timecard = $this->repository->findOneBy(array(
            'label' => $uniqueString,
            'perimeter' => $perimeter->getId()
        ));

        if ($timecard == null) {
            $timecard = new Timecard();
            $timecard->setPerimeter($perimeter);
            $timecard->setLabel($uniqueString);
        }

        return $timecard;
    }

    /**
     * @param $timecardId
     */
    public function remove($timecardId)
    {
        $timecard = $this->repository->find($timecardId);

        //$this->timecardPdfManager->removeTimecardPdfByTimecard($timecard);
        $this->om->remove($timecard);
        $this->om->flush();
    }

    /**
     * @param $timecard
     * @param $user
     * @param $externaNetworkId
     */
    public function save($timecard, $user, $externaNetworkId)
    {
        $perimeter = $this->perimeterManager->findOneByExternalNetworkId(
            $user->getCustomer(),
            $externaNetworkId
        );

        $timecard->setPerimeter($perimeter);
        $this->om->persist($timecard);
        $this->om->flush();
    }
}