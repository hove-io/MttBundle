<?php

namespace CanalTP\MttBundle\Services;

use Doctrine\Common\Persistence\ObjectManager;
use CanalTP\MttBundle\Entity\AmqpTask;
use CanalTP\MttBundle\Entity\AreaPdf;
use CanalTP\MttBundle\Entity\Season;
use CanalTP\MttBundle\Entity\Area;

class AreaPdfManager
{
    private $om = null;
    private $request = null;
    private $uploadPath = null;
    private $repository = null;
    private $taskManager = null;

    public function __construct(ObjectManager $om, $request, $uploadPath, TaskManager $taskManager)
    {
        $this->om = $om;
        $this->request = $request;
        $this->repository = $om->getRepository('CanalTPMttBundle:AreaPdf');
        $this->uploadPath = $uploadPath;
        $this->taskManager = $taskManager;
    }

    public function findAll()
    {
        return ($this->repository->findAll());
    }

    private function getUploadRootDir()
    {
        return $this->uploadPath;
    }

    public function generateRelativeAreaPdfPath($areaPdf)
    {
        $path = $this->request->getCurrentRequest()->getBasePath() . '/uploads/';
        $path .= $areaPdf->getPath();

        return $path;
    }

    public function generateAbsoluteAreaPdfPath($areaPdf)
    {
        $path = $this->getUploadRootDir();
        $path .= $areaPdf->getPath();

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

        if ($areaPdf == null) {
            $areaPdf = new AreaPdf();

            $areaPdf->setArea($area);
            $areaPdf->setSeason($season);
            $this->om->persist($areaPdf);
            $this->om->flush();
        }

        return ($areaPdf);
    }

    private function removeAreaPdf($areaPdf)
    {
        $path = $this->generateAbsoluteAreaPdfPath($areaPdf);

        $this->taskManager->remove($areaPdf->getId(), AmqpTask::AREA_PDF_GENERATION_TYPE);
        if (file_exists($path)) {
            unlink($path);
        }
    }

    public function removeAreaPdfBySeason(Season $season)
    {
        $areaPdfs = $this->repository->findBySeason($season);

        foreach ($areaPdfs as $areaPdf) {
            $this->removeAreaPdf($areaPdf);
        }
    }

    public function removeAreaPdfByArea(Area $area)
    {
        $areaPdfs = $this->repository->findByArea($area);

        foreach ($areaPdfs as $areaPdf) {
            $this->removeAreaPdf($areaPdf);
        }
    }
}
