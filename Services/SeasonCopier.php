<?php

namespace CanalTP\MttBundle\Services;

use Doctrine\Common\Persistence\ObjectManager;

class SeasonCopier
{
    private $om = null;
    private $lineManager = null;
    private $stopTimetableManager = null;
    private $blockManager = null;
    private $frequencyManager = null;
    private $stopPointManager = null;

    public function __construct(
        ObjectManager $om,
        LineManager $lineManager,
        StopTimetableManager $stopTimetableManager,
        StopPointManager $stopPointManager,
        BlockManager $blockManager,
        FrequencyManager $frequencyManager
    )
    {
        $this->om = $om;
        $this->lineManager = $lineManager;
        $this->stopTimetableManager = $stopTimetableManager;
        $this->stopPointManager = $stopPointManager;
        $this->blockManager = $blockManager;
        $this->frequencyManager = $frequencyManager;
    }

    public function copyBlocksForStopPoint($origStopPoint, $destStopPoint, $destStopTimetable)
    {
        foreach ($origStopPoint->getBlocks() as $origBlock) {
            $this->blockManager->copy($origBlock, $destStopTimetable, $destStopPoint);
        }
    }

    public function copyBlocksForStopTimetable($origStopTimetable, $destStopTimetable)
    {
        $origBlocks = $this->om->getRepository('CanalTPMttBundle:StopTimetable')
            ->findBlocksByStopTimetableIdOnly($origStopTimetable->getId());

        foreach ($origBlocks as $origBlock) {
            $this->blockManager->copy($origBlock, $destStopTimetable);
        }
    }

    public function copyStopPoints($origStopTimetable, $destStopTimetable)
    {
        $stopPoints = $this->om->getRepository('CanalTPMttBundle:StopPoint')
            ->findByStopTimetable($origStopTimetable);

        foreach ($stopPoints as $origStopPoint) {
            $destStopPoint = $this->stopPointManager->copy($origStopPoint, $destStopTimetable);

            $this->copyBlocksForStopPoint($origStopPoint, $destStopPoint, $destStopTimetable);
        }
        $this->copyBlocksForStopTimetable($origStopTimetable, $destStopTimetable);
    }

    public function copyStopTimetables($origLineConfig, $destLineConfig)
    {
        foreach ($origLineConfig->getStopTimetables() as $origStopTimetable) {
            $destStopTimetable = $this->stopTimetableManager->copy($origStopTimetable, $destLineConfig);

            $this->copyStopPoints($origStopTimetable, $destStopTimetable);
        }
    }

    public function copyLineConfigs($lineConfigs, $destSeason)
    {
        foreach ($lineConfigs as $origLineConfig) {
            $destLineConfig = $this->lineManager->copy($origLineConfig, $destSeason);

            $this->copyStopTimetables($origLineConfig, $destLineConfig);
        }
    }

    public function run($origSeasonId, $destSeason)
    {
        $season = $this->om->getRepository('CanalTPMttBundle:Season')->find($origSeasonId);
        $this->copyLineConfigs($season->getLineConfigs(), $destSeason);
        $this->om->flush();
    }
}
