<?php

/**
 * Description of Network
 *
 * @author rabikhalil
 */
namespace CanalTP\MttBundle\Services;

use Symfony\Component\DependencyInjection\Container;
use Doctrine\Common\Persistence\ObjectManager;

class LineManager
{
    private $line = null;
    private $navitia = null;
    private $repository = null;
    private $container = null;

    public function __construct(Container $co, ObjectManager $om, Navitia $navitia)
    {
        $this->line = null;
        $this->container = $co;
        $this->navitia = $navitia;
        $this->repository = $om->getRepository('CanalTPMttBundle:LineConfig');
    }

    private function initTwigPath()
    {
        $layouts = $this->container->getParameter('layouts');

        $this->line->setTwigPath($layouts[$this->line->getLayout()]['twig']);
    }

    /**
     * Return line Object with navitia data added
     *
     * @param  string $externalLineId
     * @return line
     */
    public function getLineConfigByExternalLineId($externalLineId)
    {
        $this->line = $this->repository->findOneByExternalLineId($externalLineId);

        $this->initTwigPath();

        return $this->line;
    }

    /**
     * Return line Object with navitia data added
     *
     * @param  string $externalLineId
     * @return line
     */
    public function getLineConfigByExternalLineIdAndSeasonId($externalLineId, $seasonId)
    {
        $this->line = $this->repository->findOneBy(
            array(
                'externalLineId' => $externalLineId,
                'season' => $seasonId
            )
        );

        $this->initTwigPath();

        return $this->line;
    }

    /**
     * Return line Object with navitia data added
     *
     * @param  Integer $lineId
     * @return line
     */
    public function getLine($lineId)
    {
        $this->line = $this->repository->find($lineId);

        $this->initTwigPath();

        return $this->line;
    }
}
