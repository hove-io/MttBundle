<?php

namespace CanalTP\MttBundle\MediaManager\Category;

use CanalTP\MediaManager\Category\AbstractCategory;

class StopPointCategory extends AbstractCategory
{
    public function __construct()
    {
        parent::__construct();

        $this->id = 'stop_point';
        $this->name = 'Stop Point';
        $this->ressourceId = 'stop_points';
    }
}
