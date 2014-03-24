<?php

namespace CanalTP\MttBundle\Services;

class SeasonCopier
{
    private $lineManager = null;
    private $timetableManager = null;
    private $blockManager = null;
    private $frequencyManager = null;
    private $mediaManager = null;

    public function __construct(
        LineManager $lineManager,
        TimetableManager $timetableManager,
        BlockManager $blockManager,
        FrequencyManager $frequencyManager
    )
    {
        $this->lineManager = $lineManager;
        $this->timetableManager = $timetableManager;
        $this->blockManager = $blockManager;
        $this->frequencyManager = $frequencyManager;
        $this->mediaManager = $mediaManager;
    }

    public function run($origSeasonId, $destSeasonId)
    {
    }
}