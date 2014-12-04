<?php

/**
 * Description of Network
 *
 * @author rabikhalil
 */
namespace CanalTP\MttBundle\Services;

use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class LineManager
{
    private $lineConfig = null;
    private $repository = null;
    private $om = null;

    public function __construct(ObjectManager $om)
    {
        $this->lineConfig = null;
        $this->om = $om;
        $this->repository = $this->om->getRepository('CanalTPMttBundle:LineConfig');
    }

    public function initTwigPath($lineConfig)
    {
        if (empty($lineConfig)) {
            throw new NotFoundHttpException("LineConfig not found");
        }
        $lineConfig->setTwigPath($lineConfig->getLayoutConfig()->getLayout()->getPath());
    }

    public function getLineConfigWithSeasonByExternalLineId($externalLineId, $season)
    {
        return $this->repository
            ->getLineConfigByExternalLineIdAndSeason(
                $externalLineId,
                $season
            );
    }

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

    public function getLineConfig($lineConfigId)
    {
        $this->lineConfig = $this->repository->find($lineId);

        $this->initTwigPath($this->lineConfig);

        return $this->lineConfig;
    }

    public function find($id)
    {
        return ($this->repository->find($id));
    }

    public function save($lineConfig, $season, $externalLineId)
    {
        $lineConfig->setExternalLineId($externalLineId);
        $lineConfig->setSeason($season);
        $this->om->persist($lineConfig);
        $this->om->flush();
    }

    public function copy($lineConfig, $destSeason)
    {
        $lineConfigCloned = clone $lineConfig;
        $lineConfigCloned->setSeason($destSeason);

        $this->om->persist($lineConfigCloned);

        return $lineConfigCloned;
    }
}
