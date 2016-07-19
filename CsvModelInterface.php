<?php

namespace CanalTP\MttBundle;

interface CsvModelInterface
{
    /**
     * Get Headers of the csv
     *
     * @return array
     */
    public function getHeaders();

    /**
     * Get Rows of the csv
     *
     * @return array
     */
    public function getRows();

    /**
     * Get filename of the csv
     *
     * @return array
     */
    public function getFilename();
}
