<?php

/**
 * Description of RouteManager
 *
 * @author vdegroote
 */
namespace CanalTP\MethBundle\Services;

use Symfony\Component\DependencyInjection\Container;
use Doctrine\Common\Persistence\ObjectManager;
use CanalTP\MethBundle\Services\Navitia;
use CanalTP\MethBundle\Entity\Route;

class RouteManager
{
    private $route = null;
    private $line = null;
    private $navitia = null;
    private $repository = null;
    private $container = null;

    public function __construct(Container $co, ObjectManager $om, Navitia $navitia, LineManager $lineManager)
    {
        $this->route = null;
        $this->container = $co;
        $this->navitia = $navitia;
        $this->lineManager = $lineManager;
        $this->repository = $om->getRepository('CanalTPMethBundle:Route');
    }
    
    /*
     * @function find twig path for parent line
     */
    private function initAdditionalData($externalId, $externalCoverageId)
    {
        $data = $this->navitia->getRouteData($externalId, $externalCoverageId);
        $line = $this->lineManager->getLineByExternalId($data->line->id);
        $this->route->setLine($line);
        $this->route->setTitle($data->name);
    }
    
    /*
     * @function init route object, if not found in dbb create a non persistent yet entity
     */
    private function initRoute($externalId, $externalCoverageId)
    {
        $this->route = $this->repository->findByExternalId($externalId);
        if (empty($this->route))
        {
            $this->route = new Route();
            $this->route->setExternalId($externalId);
        }
    }
    
    /*
     * @function if route has an id, get corresponding blocks and index them by dom_id
     */
    private function initBlocks()
    {
        if ($this->route->getId())
        {
            $blocks = array();

            foreach ($this->route->getBlocks() as $block) {
                $blocks[$block->getDomId()] = $block;
            }
            $this->route->setBlocks($blocks);
        }
    }
    
    /**
     * Return Route Object with navitia data added
     *
     * @param  Integer $externalId
     * @return line
     */
    public function getRoute($externalId, $externalCoverageId)
    {
        $this->initRoute($externalId, $externalCoverageId);
        $this->initAdditionalData($externalId, $externalCoverageId);
        $this->initBlocks();

       return $this->route;
    }
}