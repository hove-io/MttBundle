<?php

namespace CanalTP\MttBundle\Calendar;

use CanalTP\MttBundle\CsvGenerator;
use CanalTP\MttBundle\CsvModelInterface;
use ZipArchive;

class CalendarArchiveGenerator
{
    /**
     * @var ZipArchive
     */
    private $archive;

    /**
     * CalendarArchiveGenerator constructor.
     *
     * @param string $location Archive filename
     * @param \ZipArchive $archive Zip Archive
     */
    public function __construct($location, ZipArchive $archive = null)
    {
        $this->setArchive($archive ?: new ZipArchive());
        $this->openArchive($location);
    }

    /**
     * ZipArchive setter.
     *
     * @param ZipArchive $archive
     */
    public function setArchive(ZipArchive $archive)
    {
        $this->archive = $archive;
    }

    /**
     * Open a zip file.
     *
     * @param $location
     *
     * @throws \LogicException if it cannot open the archive
     */
    public function openArchive($location)
    {
        $location = str_replace('/', DIRECTORY_SEPARATOR, $location);
        if (($response = $this->archive->open($location, ZipArchive::CREATE)) !== true) {
            throw new \LogicException('Could not open zip archive at:'.$location.', error: '.$response);
        }
    }

    /**
     * Get the used ZipArchive.
     *
     * @return ZipArchive
     */
    public function getArchive()
    {
        return $this->archive;
    }

    /**
     * Add a Csv
     *
     * @param CsvModelInterface $csvModel
     */
    public function addCsv(CsvModelInterface $csvModel)
    {
        $this->archive->addFromString(
            $csvModel->getFilename(),
            CsvGenerator::generateCSV($csvModel)
        );
    }
}
