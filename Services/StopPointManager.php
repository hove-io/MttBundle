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
    private $om = null;
    private $container = null;

    public function __construct(Container $co, ObjectManager $om, Navitia $navitia)
    {
        $this->stopPoint = null;
        $this->container = $co;
        $this->navitia = $navitia;
        $this->repository = $om->getRepository('CanalTPMethBundle:StopPoint');
        $this->om = $om;
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
     * Return StopPoint with data from navitia
     *
     * @param  String $stopPointNavitiaId
     * @param  Line $line Line Entity
     * @return stopPoint
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
    
    /**
     * Return StopPoints list with Data from navitia
     *
     * @param  array $stopPointNavitiaIds Array of Stoppoints NavitiaId
     * @param  Line $line Object entity0
     * @return array
     */
    public function enhanceStopPoints($stopPoints, $line)
    {
        $ids = array();
        $stopPointsIndexed = array();
        // extract ids to prepare SQL WHERE IN 
        foreach($stopPoints as $stopPoint_data)
        {
            $ids[] = $stopPoint_data->stop_point->id;
            // and index by navitia Id to make it easy to find an item inside
            $stopPointsIndexed[$stopPoint_data->stop_point->id] = $stopPoint_data;
        }
        $query = $this->om
            ->createQueryBuilder()
            ->addSelect('stopPoint')
            ->where("stopPoint.navitiaId IN(:ids)")
            ->from('CanalTPMethBundle:StopPoint', 'stopPoint')
            ->setParameter('ids', array_values($ids))
            ->getQuery();

        $db_stop_points = $query->getResult();
        foreach ($db_stop_points as $db_stop_point)
        {
            if (isset($stopPointsIndexed[$db_stop_point->getNavitiaId()]))
            {
                $stopPointsIndexed[$db_stop_point->getNavitiaId()]->stop_point->pdfGenerationDate = $db_stop_point->getPdfGenerationDate();
            }
        }
        return $stopPointsIndexed;
    }
}
