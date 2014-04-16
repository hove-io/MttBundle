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
    private $lineConfig = null;
    private $navitia = null;
    private $repository = null;
    private $om = null;
    private $container = null;

    public function __construct(Container $co, ObjectManager $om, Navitia $navitia)
    {
        $this->lineConfig = null;
        $this->container = $co;
        $this->navitia = $navitia;
        $this->om = $om;
        $this->repository = $this->om->getRepository('CanalTPMttBundle:LineConfig');
    }

    public function initTwigPath($lineConfig)
    {
        $lineConfig->setTwigPath($lineConfig->getLayout()->getTwig());
    }

    /**
     * Return line Object with navitia data added
     *
     * @param  string $externalLineId
     * @return line
     */
    public function getLineConfigByExternalLineIdAndSeasonId($externalLineId, $seasonId)
    {
        $this->lineConfig = $this->repository->findOneBy(
            array(
                'externalLineId' => $externalLineId,
                'season' => $seasonId
            )
        );

        $this->initTwigPath($this->lineConfig);

        return $this->lineConfig;
    }

    /**
     * Return line Object with navitia data added
     *
     * @param  Integer $lineId
     * @return line
     */
    public function getLineConfig($lineConfigId)
    {
        $this->lineConfig = $this->repository->find($lineId);

        $this->initTwigPath();

        return $this->lineConfig;
    }

    public function save($lineConfig, $season, $externalLineId)
    {
        // $lineConfig->setLayout($this->om->getPartialReference('CanalTP\MttBundle\Entity\Layout', $lineConfig->getLayout()));
        $lineConfig->setExternalLineId($externalLineId);
        $lineConfig->setSeason($season);
        $this->om->persist($lineConfig);
        $this->om->flush();
    }

    /**
     * Return line Object with navitia data added
     *
     * @param  Object $lineConfig
     * @param  Object $destSeason
     * @return line
     */
    public function copy($lineConfig, $destSeason)
    {
        $lineConfigCloned = clone $lineConfig;
        $lineConfigCloned->setSeason($destSeason);

        $this->om->persist($lineConfigCloned);

        return $lineConfigCloned;
    }
}
