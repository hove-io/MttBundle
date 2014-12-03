<?php

namespace CanalTP\MttBundle\Services;

use Doctrine\Common\Persistence\ObjectManager;
use CanalTP\MttBundle\Entity\AreaPdf;

class AreaPdfManager
{
    private $om = null;
    private $request = null;
    private $uploadPath = null;
    private $repository = null;

    public function __construct(ObjectManager $om, $request, $uploadPath)
    {
        $this->om = $om;
        $this->request = $request;
        $this->repository = $om->getRepository('CanalTPMttBundle:AreaPdf');
        $this->uploadPath = $uploadPath;
    }

    public function findAll()
    {
        return ($this->repository->findAll());
    }

    private function getUploadRootDir()
    {
        return $this->uploadPath;
    }

    public function generateAreaPdfPath($areaPdf)
    {
        $path = 'area/';
        $path .= $areaPdf->getArea()->getId() . '/';
        $path .= 'seasons/';
        $path .= $areaPdf->getSeason()->getId() . '/';
        $path .= 'Secteur.pdf';

        return $path;
    }

    public function generateRelativeAreaPdfPath($areaPdf)
    {
        $path = $this->request->getCurrentRequest()->getBasePath() . '/uploads/';
        $path .= $this->generateAreaPdfPath($areaPdf);

        return $path;
    }

    public function generateAbsoluteAreaPdfPath($areaPdf)
    {
        $path = $this->getUploadRootDir();
        $path .= $this->generateAreaPdfPath($areaPdf);

        return $path;
    }

    public function findPdfPath($areaPdf)
    {
        $absPath = $this->generateAbsoluteAreaPdfPath($areaPdf);
        $relPath = $this->generateRelativeAreaPdfPath($areaPdf);

        return (file_exists($absPath) ? $relPath : null);
    }

    public function getAreaPdf($area, $season)
    {
        $areaPdf = $this->repository->findOneBy(
            array(
                'area' => $area->getId(),
                'season' => $season->getId(),
            )
        );

        if ($areaPdf == null)
        {
            $areaPdf = new AreaPdf();

            $areaPdf->setArea($area);
            $areaPdf->setSeason($season);
            $this->om->persist($areaPdf);
            $this->om->flush();
        }

        return ($areaPdf);
    }

}
