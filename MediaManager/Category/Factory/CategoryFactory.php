<?php

namespace CanalTP\MttBundle\MediaManager\Category\Factory;

use CanalTP\MediaManager\Category\Factory\CategoryFactoryInterface;
use CanalTP\MttBundle\MediaManager\Category\CategoryType;
use CanalTP\MttBundle\MediaManager\Category\NetworkCategory;
use CanalTP\MttBundle\MediaManager\Category\RouteCategory;
use CanalTP\MttBundle\MediaManager\Category\StopPointCategory;
use CanalTP\MttBundle\MediaManager\Category\SeasonCategory;

class CategoryFactory implements CategoryFactoryInterface
{
    private function product($type)
    {
        $category = null;

        switch ($type) {
            case CategoryType::NETWORK:
                $category = new NetworkCategory();
                break;
            case CategoryType::ROUTE:
                $category = new RouteCategory();
                break;
            case CategoryType::STOP_POINT:
                $category = new StopPointCategory();
                break;
            case CategoryType::SEASON:
                $category = new SeasonCategory();
                break;
        }

        return ($category);
    }

    public function create($type)
    {
        return ($this->product($type));
    }
}
