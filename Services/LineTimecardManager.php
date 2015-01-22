<?php

namespace CanalTP\MttBundle\Services;

use Doctrine\Common\Persistence\ObjectManager;
use CanalTP\MttBundle\Entity\LineTimecard as LineTimecard;
use CanalTP\MttBundle\Entity\LineConfig as LineConfig;


/**
 * Class LineTimecardManager
 * @package CanalTP\MttBundle\Services
 */
class LineTimecardManager
{
    private $om = null;
    private $perimeterManager = null;
    private $user = null;

    /**
     * @param ObjectManager $om
     * @param $perimeterManager
     * @param $securityContext
     */
    public function __construct(ObjectManager $om, $perimeterManager, $securityContext)
    {
        $this->om = $om;
        $this->perimeterManager = $perimeterManager;
        $this->user = $securityContext->getToken()->getUser()->getCustomer();

    }

    /**
     * Create LineTimecard if not exist.
     *
     * @param $lineId
     * @param $networkId
     * @param $lineConfig
     * @return LineTimecard
     */
    public function createLineTimecardIfNotExist($lineId, $networkId, LineConfig $lineConfig) {

        $perimeter = $this->perimeterManager->findOneByExternalNetworkId(
            $this->user,
            $networkId
        );

        $lineTimecard = $this->om->getRepository('CanalTPMttBundle:LineTimecard')->findOneBy(
            array(
                'line_id' => $lineId,
                'perimeter' => $perimeter
            )
        );

        if (empty($lineTimecard)) {
            $lineTimecard = new LineTimecard();
            $lineTimecard->setLineId($lineId)->setPerimeter($perimeter)->setLineConfig($lineConfig);
            $this->om->persist($lineTimecard);
            $this->om->flush($lineTimecard);
        }

        return $lineTimecard;
    }

    /**
     * Get LineTimecard.
     *
     * @param $lineId
     * @param $networkId
     * @return LineTimecard
     */
    public function getLineTimecard($lineId, $networkId)
    {

        $perimeter = $this->perimeterManager->findOneByExternalNetworkId(
            $this->user,
            $networkId
        );

        $lineTimecard = $this->om->getRepository('CanalTPMttBundle:LineTimecard')->findOneBy(
            array(
                'line_id' => $lineId,
                'perimeter' => $perimeter
            )
        );

        return $lineTimecard;
    }
}