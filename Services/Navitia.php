<?php

/**
 * Description of Network
 *
 * @author vdegroote
 */
namespace CanalTP\MethBundle\Services;

class Navitia
{
    protected $navitia_component;
    protected $navitia_iussaad;

    public function __construct($navitia_component, $navitia_iussaad)
    {
        $this->navitia_component = $navitia_component;
        $this->navitia_iussaad = $navitia_iussaad;
    }
    
    /**
     * Returns Lines indexed by modes
     *
     * @param  String $coverageId
     * @param  type $networkId
     * @param  Boolean $commercial if true commercial_modes returned, else physical_modes
     * @return type
     */
    public function getLinesByMode($coverageId, $networkId, $commercial = true)
    {
        $result = $this->navitia_iussaad->getLines($coverageId, $networkId, 1);
        $lines_by_modes = array();
        foreach ($result->lines as $line)
        {
            if (!isset($lines_by_modes[$line->commercial_mode->id]))
            {
                $lines_by_modes[$line->commercial_mode->id] = array();
            }
            $lines_by_modes[$line->commercial_mode->id][] = $line;
        }
        return $lines_by_modes;
    }

    /**
     * Returns line data
     *
     * @param  String $coverageId
     * @param  String $networkId
     * @param  String $lineId
     * @return type
     */
    public function getLineTitle($coverageId, $networkId, $lineId)
    {
        $response = $this->navitia_iussaad->getLine($coverageId, $networkId, $lineId);

        return ($response->lines[0]->name);
    }
}
