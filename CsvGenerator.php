<?php

namespace CanalTP\MttBundle;

use CanalTP\MttBundle\CsvModelInterface;
use League\Csv\Reader;
use League\Csv\Writer;

class CsvGenerator
{
    public static function generateCSV(CsvModelInterface $csvModel)
    {
        $csv = Writer::createFromFileObject(new \SplTempFileObject());
        $csv->setDelimiter(';');
        $csv->insertOne($csvModel->getHeaders());
        $csv->insertAll($csvModel->getRows());

        return (string) $csv;
    }
}
