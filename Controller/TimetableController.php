<?php

namespace CanalTP\MethBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use CanalTP\MethBundle\Entity\Line;

class TimetableController extends Controller
{
    /*
     * Display a layout and make editable via javascript
     */
    public function editAction($line_id, $stopPoint = null)
    {
        $lineManager = $this->get('canal_tp_meth.line_manager');
        $line = $lineManager->getLine($line_id);
        // are we on stop_point level?
        if ($stopPoint != '')
        {
            $stopPointManager = $this->get('canal_tp_meth.stop_point_manager');
            $stopPoint_instance = $stopPointManager->getStopPoint($stopPoint, $line);
            $stopPointLevel = true;
        }
        else
        {
            $stopPointLevel = false;
            $stopPoint_instance = false;
        }
        return $this->render(
            'CanalTPMethBundle:Layouts:' . $line->getTwigPath(),
            array(
                'line'            => $line,
                'stopPoint'       => $stopPoint_instance,
                'stopPointLevel'  => $stopPoint,
                'blockTypes'      => $this->container->getParameter('blocks')
            )
        );
    }
}
