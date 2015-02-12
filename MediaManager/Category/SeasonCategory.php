<?php

namespace CanalTP\MttBundle\MediaManager\Category;

use CanalTP\MediaManager\Category\AbstractCategory;

class SeasonCategory extends AbstractCategory
{
    public function __construct()
    {
        parent::__construct();

        $this->id = 'season';
        $this->name = 'Season';
        $this->ressourceId = 'seasons';
    }
}
