<?php

namespace CanalTP\MttBundle\MediaManager\Category;

use CanalTP\MediaManager\Category\AbstractCategory;

class RouteCategory extends AbstractCategory
{
    public function __construct()
    {
        parent::__construct();

        $this->id = 'route';
        $this->name = 'Route';
        $this->ressourceId = 'routes';
    }
}
