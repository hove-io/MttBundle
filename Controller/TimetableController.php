<?php

namespace CanalTP\MethBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use CanalTP\MethBundle\Entity\Line;

class TimetableController extends Controller
{
    private function getLine($line_id)
    {
        $lineManager = $this->get('canal_tp_meth.line_manager');
        return $lineManager->getLine($line_id);
    }
    
    private function getStopPoint($line, $stopPoint)
    {
        // are we on stop_point level?
        if ($stopPoint != '') {
            $stopPointLevel = true;
            $stopPointManager = $this->get('canal_tp_meth.stop_point_manager');
            $stopPointInstance = $stopPointManager->getStopPoint($stopPoint, $line);
        } else {
            $stopPointLevel = false;
            $stopPointInstance = false;
        }
        return array(
            'stopPointLevel' => $stopPointLevel,
            'stopPointInstance' => $stopPointInstance
        );
    }
    
    /*
     * Display a layout
     */
    public function viewAction($line_id, $stopPoint = null)
    {
        $line = $this->getLine($line_id);
        $stopPointData = $this->getStopPoint($line, $stopPoint);
        // var_dump($stopPointData);die;
        return $this->render(
            'CanalTPMethBundle:Layouts:' . $line->getTwigPath(),
            array(
                'line'            => $line,
                'stopPointLevel'  => $stopPointData['stopPointLevel'],
                'stopPoint'       => $stopPointData['stopPointInstance'],
                'editable'        => false
            )
        );
    }
    
    /*
     * Display a layout and make editable via javascript
     */
    public function editAction($line_id, $stopPoint = null)
    {
        $line = $this->getLine($line_id);
        $stopPointData = $this->getStopPoint($line, $stopPoint);
        
        return $this->render(
            'CanalTPMethBundle:Layouts:' . $line->getTwigPath(),
            array(
                'line'            => $line,
                'stopPointLevel'  => $stopPointData['stopPointLevel'],
                'stopPoint'       => $stopPointData['stopPointInstance'],
                'blockTypes'      => $this->container->getParameter('blocks'),
                'editable'        => true
            )
        );
    }
}
