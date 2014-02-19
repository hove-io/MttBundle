<?php

/**
 * Description of Network
 *
 * @author rabikhalil
 */
namespace CanalTP\MethBundle\Services;

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
        $this->repository = $om->getRepository('CanalTPMethBundle:Line');
    }

    private function initTwigPath()
    {
        $layouts = $this->container->getParameter('layouts');

        $this->line->setTwigPath($layouts[$this->line->getLayout()]['twig']);
    }

    /**
     * Return line Object with navitia data added
     *
     * @param  string $lineExternalId
     * @return line
     */
    public function getLineByExternalId($lineExternalId)
    {
        $this->line = $this->repository->findOneByExternalId($lineExternalId);

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
