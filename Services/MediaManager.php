<?php

namespace CanalTP\MttBundle\Services;

use CanalTP\MediaManagerBundle\DataCollector\MediaDataCollector;

class MediaManager
{
    private $mediaManager = null;

    public function __construct(MediaDataCollector $mediaManager)
    {
        $this->mediaManager = $mediaManager;
    }
}