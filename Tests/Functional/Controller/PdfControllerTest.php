<?php

namespace CanalTP\MttBundle\Tests\Functional\Controller;

use CanalTP\MttBundle\Tests\DataFixtures\ORM\Fixture;

class PdfControllerTest extends AbstractControllerTest
{
    private function getRoute($route, $timetableId = false, $externalNetworkId = Fixture::EXTERNAL_NETWORK_ID, $externalStopPointId = Fixture::EXTERNAL_STOP_POINT_ID)
    {
        return $this->generateRoute(
            $route,
            array(
                'externalNetworkId' => Fixture::EXTERNAL_NETWORK_ID,
                'externalStopPointId' => $externalStopPointId,
                "timetableId" => $timetableId ? $timetableId : Fixture::$timetableId
            )
        );
    }
    
    public function testPdfGeneration()
    {
        $crawler = $this->doRequestRoute($this->getRoute('canal_tp_mtt_timetable_generate_pdf'), 302);
        $location = $this->client->getResponse()->headers->get('location');
        $buffer = file_get_contents($location);
        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->buffer($buffer);
        $this->assertEquals($mime, "application/pdf", "Mime type of $location should be application/pdf. Found $mime");
    }
}