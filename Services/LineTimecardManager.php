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
    private $lineTimecard = null;

    /**
     * Constructor
     *
     * @param ObjectManager $om
     * @param $perimeterManager
     * @param $securityContext
     */
    public function __construct(ObjectManager $om, $perimeterManager)
    {
        $this->om = $om;
        $this->perimeterManager = $perimeterManager;
        $this->repository = $this->om->getRepository('CanalTPMttBundle:LineTimecard');

    }

    /**
     * Create LineTimecard if not exist.
     *
     * @param $lineId
     * @param $perimeter
     * @param $lineConfig
     * @return LineTimecard
     */
    public function createLineTimecardIfNotExist($lineId, $perimeter, LineConfig $lineConfig) {

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
     * Get LineTimecard by line and network id
     *
     * @param $lineId
     * @param $perimeter
     * @return LineTimecard
     */
    public function getLineTimecard($lineId, $perimeter)
    {

        $this->lineTimecard = $this->om->getRepository('CanalTPMttBundle:LineTimecard')->findOneBy(
            array(
                'line_id' => $lineId,
                'perimeter' => $perimeter
            )
        );

        $this->initBlocks();

        return $this->lineTimecard;
    }

    /**
     * Get LineTimecard by id
     *
     * @param $objectId
     * @param null $externalCoverageId
     * @return null|object
     */
    public function getById($objectId, $externalCoverageId = null)
    {
        $this->lineTimecard = $this->repository->find($objectId);

        $this->initBlocks();

        return $this->lineTimecard;

    }

    /**
     * Get corresponding blocks and index them by dom_id
     */
    private function initBlocks()
    {
        $lineTimecardBlocks = $this->repository->findBlocksByLineTimecardIdOnly($this->lineTimecard->getId());

        if (count($lineTimecardBlocks) > 0) {
            $blocks = array();

            foreach ($lineTimecardBlocks as $block) {
                $blocks[$block->getDomId()] = $block;
            }
            if (count($blocks) > 0) {
                $this->lineTimecard->setBlocks($blocks);
            }
        }
    }
}