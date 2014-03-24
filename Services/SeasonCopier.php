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

    public function __construct(
        ObjectManager $om,
        LineManager $lineManager,
        TimetableManager $timetableManager,
        BlockManager $blockManager,
        FrequencyManager $frequencyManager,
        MediaManager $mediaManager
    )
    {
        $this->om = $om;
        $this->lineManager = $lineManager;
        $this->timetableManager = $timetableManager;
        $this->blockManager = $blockManager;
        $this->frequencyManager = $frequencyManager;
        $this->mediaManager = $mediaManager;
    }

    public function run($origSeasonId, $destSeason)
    {
        $season = $this->om->getRepository('CanalTPMttBundle:Season')->find($origSeasonId);
        foreach ($season->getLineConfigs() as $lineConfig) {
            $newLineConfig = $this->lineManager->copy($lineConfig, $destSeason);
            $this->om->persist($newLineConfig);
        }
        $this->om->flush();
    }
}