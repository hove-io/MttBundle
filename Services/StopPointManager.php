<?php

/**
 * Description of Network
 *
 * @author rabikhalil
 */
namespace CanalTP\MethBundle\Services;

use Symfony\Component\DependencyInjection\Container;
use Doctrine\Common\Persistence\ObjectManager;
use CanalTP\MethBundle\Services\Navitia;
use CanalTP\MethBundle\Entity\StopPoint;

class StopPointManager
{
    private $stopPoint = null;
    private $navitia = null;
    private $repository = null;
    private $container = null;

    public function __construct(Container $co, ObjectManager $om, Navitia $navitia)
    {
        $this->stopPoint = null;
        $this->container = $co;
        $this->navitia = $navitia;
        $this->repository = $om->getRepository('CanalTPMethBundle:StopPoint');
    }

    private function initTitle($line)
    {
        $this->stopPoint->setTitle(
            $this->navitia->getStopPointTitle(
                $line->getCoverageId(),
                $this->stopPoint->getNavitiaId()
            )
        );
    }

    // TODO: mutualize with line manager?
    private function initBlocks()
    {
        $blocks = array();

        foreach ($this->stopPoint->getBlocks() as $block) {
            $blocks[$block->getDomId()] = $block;
        }
        $this->stopPoint->setBlocks($blocks);
    }

    /**
     * Return Object of line
     *
     * @param  Integer $lineId
     * @return line
     */
    public function getStopPoint($stopPointNavitiaId, $line)
    {
        $this->stopPoint = $this->repository->findOneByNavitiaId($stopPointNavitiaId);
        if (!empty($this->stopPoint)) {
            $this->initBlocks();
            // stop points blocks override line blocks regarding dom_id, sprint 5?
            $line->setBlocks(array_merge($line->getBlocks(), $this->stopPoint->getBlocks()));
        } else {
            $this->stopPoint = new StopPoint();
            $this->stopPoint->setNavitiaId($stopPointNavitiaId);
        }
        $this->initTitle($line);

        return $this->stopPoint;
    }
}
