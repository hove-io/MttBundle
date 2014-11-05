<?php

/**
 * Service of distribution list
 *
 * @author rabikhalil
 */
namespace CanalTP\MttBundle\Services;

use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Filesystem\Filesystem;

class DistributionListManager
{
    private $repository = null;
    private $om = null;
    private $uploadPath = null;
    private $request = null;

    public function __construct(ObjectManager $om, $uploadPath, $request)
    {
        $this->om = $om;
        $this->uploadPath = $uploadPath;
        $this->request = $request;
        $this->repository = $om->getRepository('CanalTPMttBundle:DistributionList');
    }

    private function getUploadRootDir()
    {
        return $this->uploadPath;
    }

    public function findByTimetable($timetable)
    {
        return (
            $this->repository->findOneBy(array(
                'perimeter' => $timetable->getLineConfig()->getSeason()->getPerimeter(),
                'externalRouteId' => $timetable->getExternalRouteId()
                )
            )
        );
    }

    public function deleteSeasonDistributionListPdfs($season)
    {
        $path = $this->getUploadRootDir();
        $path .= $season->getPerimeter()->getExternalNetworkId() . '/';
        $path .= $season->getId() . '/';
        $fs = new Filesystem();
        $fs->remove(array($path));
    }

    public function deleteDistributionListPdf($timetable)
    {
        $path = $this->generateAbsoluteDistributionListPdfPath($timetable);
        if (file_exists($path)) {
            unlink($path);
        }
    }

    public function generateDistributionListPdfPath($timetable)
    {
        $path = $timetable->getLineConfig()->getSeason()->getPerimeter()->getExternalNetworkId() . '/';
        $path .= $timetable->getLineConfig()->getSeason()->getId() . '/';
        $path .= $timetable->getExternalRouteId() . '/';
        $path .= 'Liste de distribution.pdf';

        return $path;
    }

    public function generateRelativeDistributionListPdfPath($timetable)
    {
        $path = $this->request->getCurrentRequest()->getBasePath() . '/uploads/';
        $path .= $this->generateDistributionListPdfPath($timetable);

        return $path;
    }

    public function generateAbsoluteDistributionListPdfPath($timetable)
    {
        $path = $this->getUploadRootDir();
        $path .= $this->generateDistributionListPdfPath($timetable);

        return $path;
    }

    public function findPdfPathByTimetable($timetable)
    {
        $absPath = $this->generateAbsoluteDistributionListPdfPath($timetable);
        $relPath = $this->generateRelativeDistributionListPdfPath($timetable);

        return (file_exists($absPath) ? $relPath : null);
    }
}
