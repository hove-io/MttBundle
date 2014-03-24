<?php

namespace CanalTP\MttBundle\Services;

use Doctrine\Common\Persistence\ObjectManager;

class SeasonCopier
{
    private $om = null;
    private $lineManager = null;
    private $timetableManager = null;
    private $blockManager = null;
    private $frequencyManager = null;
    private $mediaManager = null;
    private $stopPointManager = null;

    public function __construct(
        ObjectManager $om,
        LineManager $lineManager,
        TimetableManager $timetableManager,
        StopPointManager $stopPointManager,
        BlockManager $blockManager,
        FrequencyManager $frequencyManager,
        MediaManager $mediaManager
    )
    {
        $this->om = $om;
        $this->lineManager = $lineManager;
        $this->timetableManager = $timetableManager;
        $this->stopPointManager = $stopPointManager;
        $this->blockManager = $blockManager;
        $this->frequencyManager = $frequencyManager;
        $this->mediaManager = $mediaManager;
    }


    public function copyFrequencies($origBlock, $destBlock)
    {
        foreach ($origBlock->getFrequencies() as $origFrequency) {
            $destFrequency = $this->frequencyManager->copy($origFrequency, $destBlock);

            $this->om->persist($destFrequency);
        }
    }

    public function copyBlocksForStopPoint($origStopPoint, $destStopPoint, $destTimetable)
    {
        foreach ($origStopPoint->getBlocks() as $origBlock) {
            $destBlock = $this->blockManager->copy($origBlock, $destTimetable, $destStopPoint);

            $this->om->persist($destBlock);
            $this->copyFrequencies($origBlock, $destBlock);
        }
    }

    public function copyBlocksForTimetable($origTimetable, $destTimetable)
    {
        $origBlocks = $this->om->getRepository('CanalTPMttBundle:Timetable')
            ->findBlocksByTimetableIdOnly($origTimetable->getId());

        foreach ($origBlocks as $origBlock) {
            $destBlock = $this->blockManager->copy($origBlock, $destTimetable);

            $this->om->persist($destBlock);
            $this->copyFrequencies($origBlock, $destBlock);
        }
    }

    public function copyStopPoints($origTimetable, $destTimetable)
    {
        $stopPoints = $this->om->getRepository('CanalTPMttBundle:StopPoint')
            ->findByTimetable($origTimetable);

        foreach ($stopPoints as $origStopPoint) {
            $destStopPoint = $this->stopPointManager->copy($origStopPoint, $destTimetable);

            $this->om->persist($destStopPoint);
            $this->copyBlocksForStopPoint($origStopPoint, $destStopPoint, $destTimetable);
        }
        $this->copyBlocksForTimetable($origTimetable, $destTimetable);
    }

    public function copyTimetables($origLineConfig, $destLineConfig)
    {
        foreach ($origLineConfig->getTimetables() as $origTimetable) {
            $destTimetable = $this->timetableManager->copy($origTimetable, $destLineConfig);

            $this->om->persist($destTimetable);
            $this->copyStopPoints($origTimetable, $destTimetable);
        }
    }

    public function copyLineConfigs($lineConfigs, $destSeason)
    {
        foreach ($lineConfigs as $origLineConfig) {
            $destLineConfig = $this->lineManager->copy($origLineConfig, $destSeason);

            $this->om->persist($destLineConfig);
            $this->copyTimetables($origLineConfig, $destLineConfig);
        }
    }

    public function run($origSeasonId, $destSeason)
    {
        $season = $this->om->getRepository('CanalTPMttBundle:Season')->find($origSeasonId);
        $this->copyLineConfigs($season->getLineConfigs(), $destSeason);
        $this->om->flush();
    }
}