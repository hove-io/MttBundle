<?php

/**
 * Description of BlockManager
 *
 * @author vdegroote
 */
namespace CanalTP\MttBundle\Services;

use Doctrine\Common\Persistence\ObjectManager;
use CanalTP\MttBundle\Entity\Block;

class BlockManager
{
    private $repository = null;
    private $om = null;
    private $mediaManager = null;
    private $calendarManager = null;

    public function __construct(ObjectManager $om, MediaManager $mediaManager, CalendarManager $calendarManager)
    {
        $this->om = $om;
        $this->mediaManager = $mediaManager;
        $this->calendarManager = $calendarManager;
        $this->repo = $om->getRepository('CanalTPMttBundle:Block');
    }

    public function findBlock($blockId)
    {
        return $this->repo->find($blockId);
    }

    /**
     * Finding or creating a block
     *
     * @param integer $blockId
     * @param LineTimetable|StopTimetable $timetable
     * @param array $attributes
     */
    public function findOrCreate($blockId, $timetable, $attributes)
    {
        $block = null;
        if ($blockId != -1) {
            $block = $this->repo->find($blockId);
        }

        if (empty($block)) {
            $block = new Block();
            $block->setRank($attributes['rank']);
            $block->setType($attributes['type']);
            $block->setDomId($attributes['domId']);
            $block->setExternalLineId($timetable->getLineConfig()->getExternalLineId());
            $block->setTimetable($timetable);
        }

        return $block;
    }

    /**
     * Saving a block
     * @param Block $block
     * @param integer $blocksNumber
     *
     * Creating / Saving a (new) block and synchronize ranks.
     * BlocksNumber is not null when a user want to add many blocks of the
     * same type in one shot.
     */
    public function save(Block $block, $blocksNumber = null)
    {
        if ($block->getRank() > 0) {
            $this->synchronizeRanks($block, 'add', $blocksNumber);

            if (!empty($blocksNumber) && $blocksNumber > 1) {
                for ($i = 1; $i < $blocksNumber; $i++) {
                    $newBlock = clone $block;
                    $newBlock->incRank($i);
                    $this->om->persist($newBlock);
                }
            }
        }

        $this->om->persist($block);
        $this->om->flush();
    }

    /**
     * Delete Block
     * @param Block $block
     *
     * Delete the block and synchronize the ranks of existing blocks.
     */
    public function delete(Block $block)
    {
        $this->synchronizeRanks($block, 'remove');
        $this->om->remove($block);

        $this->om->flush();
    }

    /**
     * Synchronizing the existing blocks by rank in database with the
     * deleted/inserted blocks.
     *
     * @param Block $block
     * @param string action
     * @param integer $blocksNumber
     */
    private function synchronizeRanks($block, $action, $blocksNumber = null)
    {
        if (!in_array($action, array('add','remove'))) {
            throw new \Exception('Action not supported for block persistence / deletion.');
        }

        $blocks = $this->getSuperiorRankedBlocks($block);

        foreach ($blocks as $datablock) {
            if ($action === 'add') {
                $datablock->incRank($blocksNumber);
            } else {
                $datablock->decRank();
            }

            $this->om->persist($datablock);
        }
    }

    private function getSuperiorRankedBlocks($block)
    {
        if ($block->getTimetable() instanceof StopTimetable) {
            $timetable = 'stopTimetable';
        } else {
            $timetable = 'lineTimetable';
        }

        $query = $this->repo->createQueryBuilder('b')
            ->where('b.'.$timetable.' = :timetable')
            ->andWhere('b.rank >= :rank')
            ->orderBy('b.rank')
            ->setParameter('timetable', $block->getTimetable())
            ->setParameter('rank', $block->getRank())
            ->getQuery()
        ;

        return $query->getResult();
    }

    /**
     * Return Block
     *
     * @param  Object $block
     * @param  Object $destStopTimetable
     * @param  Object $destStopPoint
     * @return block
     */
    public function copy($block, $destStopTimetable)
    {
        $destSeason = $destStopTimetable->getLineConfig()->getSeason();
        if ($block->isCalendar() AND !$this->calendarManager->isIncluded($block->getContent(), $destSeason)) {
            return false;
        }
        $blockCloned = clone $block;
        $blockCloned->setStopTimetable($destStopTimetable);

        if ($block->isImg()) {
            $this->mediaManager->copy($block, $blockCloned, $destStopTimetable);
        }

        $this->om->persist($blockCloned);

        return $blockCloned;
    }
}
