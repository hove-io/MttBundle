<?php

namespace CanalTP\MttBundle\MediaManager\Category;

use CanalTP\MediaManager\Category\AbstractCategory;

class LineCategory extends AbstractCategory
{
    public function __construct()
    {
        parent::__construct();

        $this->id = 'line';
        $this->name = 'Line';
        $this->ressourceId = 'lines';
    }
}
