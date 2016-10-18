<?php

namespace CanalTP\MttBundle\Menu;

/**
 * Description of Divider
 *
 * @author Kévin Ziemianski <kevin.ziemianski@canaltp.fr>
 */
class Divider extends BusinessMenuItem
{
    
    public function __construct()
    {
        $this->action = '';
        $this->children = array();
        $this->id = '';
        $this->name = '';
        $this->route = '';
        $this->parameters = '';
        $this->attributes = array('class' => 'divider');
    }
}
