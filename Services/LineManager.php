<?php

/**
 * Description of Network
 *
 * @author rabikhalil
 */
namespace CanalTP\MethBundle\Services;

use Doctrine\Common\Persistence\ObjectManager;
use CanalTP\MethBundle\Services\Navitia;

class LineManager
{
    private $line = null;
    private $navitia = null;
    private $repository = null;

    public function __construct(ObjectManager $om, Navitia $navitia)
    {
        $this->line = null;
        $this->navitia = $navitia;
        $this->repository = $om->getRepository('CanalTPMethBundle:Line');
    }
    
    private function initTitle()
    {
        $this->line->setTitle($this->navitia->getLineTitle(
                $this->line->getCoverageId(),
                $this->line->getNetworkId(),
                $this->line->getNavitiaId()
            )
        );        
    }
    
    private function initTwigPath()
    {
        // TODO : Get All Paths
        // $this->line->setTwigPath("");
    }

    private function initBlocks()
    {
        $blocks = array();

        foreach ($this->line->getBlocks() as $block) {
            $blocks[$block->getDomId()] = $block;
        }
        $this->line->setBlocks($blocks);
    }

    /**
     * Return Object of line
     *
     * @param  Integer $lineId
     * @return line
     */
    public function getLine($lineId)
    {
        $this->line = $this->repository->find($lineId);

        $this->initTitle();
        $this->initTwigPath();
        $this->initBlocks();


        return $this->line;
    }
}
