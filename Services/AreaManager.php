<?php

/**
 * Description of BlockManager
 *
 * @author vdegroote
 */
namespace CanalTP\MttBundle\Services;

use Doctrine\Common\Persistence\ObjectManager;
use CanalTP\MttBundle\Entity\Area;

class AreaManager
{
    private $repository = null;
    private $om = null;
    private $perimeterManager = null;
    private $request = null;

    public function __construct(ObjectManager $om, $perimeterManager, $request, $uploadPath)
    {
        $this->om = $om;
        $this->repository = $om->getRepository('CanalTPMttBundle:Area');
        $this->perimeterManager = $perimeterManager;
        $this->request = $request;
        $this->uploadPath = $uploadPath;
    }

    public function findAll()
    {
        return ($this->repository->findAll());
    }

    public function getAreaWithPerimeter($perimeter, $areaId)
    {
        $area = $this->find($areaId);

        if ($area == null) {
            $area = new Area();

            $area->setPerimeter($perimeter);
        }

        return ($area);
    }

    public function findByPerimeter($perimeter)
    {
        return $this->repository->findByPerimeter($perimeter);
    }

    public function find($areaId)
    {
        return empty($areaId) ? null : $this->repository->find($areaId);
    }

    public function remove($areaId)
    {
        $area = $this->repository->find($areaId);

        $this->om->remove($area);
        $this->om->flush();
    }

    public function save($area, $user, $externaNetworkId)
    {
        $perimeter = $this->perimeterManager->findOneByExternalNetworkId(
            $user->getCustomer(),
            $externaNetworkId
        );

        $area->setPerimeter($perimeter);
        $this->om->persist($area);
        $this->om->flush();
    }

    private function getUploadRootDir()
    {
        return $this->uploadPath;
    }

    public function generateAreaPdfPath($area)
    {
        $path = 'area/';
        $path .= $area->getId() . '/';
        $path .= 'Secteur.pdf';

        return $path;
    }

    public function generateRelativeAreaPdfPath($area)
    {
        $path = $this->request->getCurrentRequest()->getBasePath() . '/uploads/';
        $path .= $this->generateAreaPdfPath($area);

        return $path;
    }

    public function generateAbsoluteAreaPdfPath($area)
    {
        $path = $this->getUploadRootDir();
        $path .= $this->generateAreaPdfPath($area);

        return $path;
    }

    public function findPdfPathByTimetable($area)
    {
        $absPath = $this->generateAbsoluteAreaPdfPath($area);
        $relPath = $this->generateRelativeAreaPdfPath($area);

        return (file_exists($absPath) ? $relPath : null);
    }
}
