<?php

namespace CanalTP\MttBundle\Monitoring;

use CanalTP\SamMonitoringComponent\StateMonitorInterface as State;
use CanalTP\SamMonitoringComponent\Component\AbstractComponentMonitor;

class Component extends AbstractComponentMonitor
{
    public function __construct()
    {
        parent::__construct();

        $this->name = 'TimeTable';
    }

    private function initServicesState($category)
    {
        foreach ($category->getServices() as $service) {
            if ($service->getState() == State::DOWN) {
                $this->setState(State::DOWN);
                break;
            }
        }
    }

    private function initCategoriesState()
    {
        foreach ($this->categories as $category) {
            $this->initServicesState($category);
            if ($this->getState() == State::DOWN) {
                break;
            }
        }
    }

    public function check()
    {
        parent::check();

        $this->initCategoriesState();
        $this->setState(($this->getState() == State::UNKNOWN) ? State::UP : $this->getState());
    }
}
