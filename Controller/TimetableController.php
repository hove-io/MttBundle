<?php

namespace CanalTP\MethBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use CanalTP\MethBundle\Entity\Line;

class TimetableController extends Controller
{
    /*
     * Display a layout and make editable via javascript
     */
    public function editAction($line_id, $stop_point = null)
    {
        $lineManager = $this->get('canal_tp_meth.line_manager');
        $line = $lineManager->getLine($line_id);

        return $this->render(
            'CanalTPMethBundle:Layouts:' . $line->getTwigPath(),
            array(
                'line'  => $line,
                'blockTypes'  => $this->container->getParameter('blocks')
            )
        );
    }
}
