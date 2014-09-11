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
                "timetableId" => $timetableId != false ? $timetableId : Fixture::$timetableId
            )
        );
    }

    public function testMttHomepage()
    {
        $this->generateRoute('canal_tp_mtt_homepage');
    }

    // TODO: Need to change Jenkins coniguration because we have 403 Forbiden (authentification).
    // public function testPdfGeneration()
    // {
    //     $crawler = $this->doRequestRoute($this->getRoute('canal_tp_mtt_timetable_generate_pdf'), 302);

    //     $location = $this->client->getResponse()->headers->get('location');
    //     $buffer = file_get_contents($location);
    //     $finfo = new \finfo(FILEINFO_MIME_TYPE);
    //     $mime = $finfo->buffer($buffer);
    //     $this->assertEquals($mime, "application/pdf", "Mime type of $location should be application/pdf. Found $mime");
    // }

    /* public function testPdfNotChangingWhenModifyingSeasonTitle()
    {
        $crawler = $this->doRequestRoute($this->getRoute('canal_tp_mtt_timetable_generate_pdf'), 302);
        $location = $this->client->getResponse()->headers->get('location');
        $pdf1 = file_get_contents($location);
        $season = $this->getRepository('CanalTPMttBundle:Season')->find(Fixture::SEASON_ID);
        $season->setTitle('blablabla');
        $this->getEm()->persist($season);
        $this->getEm()->flush();
        $crawler = $this->doRequestRoute($this->getRoute('canal_tp_mtt_timetable_generate_pdf'), 302);
        $location = $this->client->getResponse()->headers->get('location');
        $pdf2 = file_get_contents($location);
        $this->assertEquals($pdf1, $pdf2, "Pdf content shouldn't change after modifying season title.");
    }

    public function testPdfChangingWhenModifyingSeasonDates()
    {
        $crawler = $this->doRequestRoute($this->getRoute('canal_tp_mtt_timetable_generate_pdf'), 302);
        $location = $this->client->getResponse()->headers->get('location');
        $pdf1 = file_get_contents($location);
        $season = $this->getRepository('CanalTPMttBundle:Season')->find(Fixture::SEASON_ID);
        $season->setEndDate(new \DateTime("+3 years"));
        $this->getEm()->persist($season);
        $this->getEm()->flush();
        $crawler = $this->doRequestRoute($this->getRoute('canal_tp_mtt_timetable_generate_pdf'), 302);
        $location = $this->client->getResponse()->headers->get('location');
        $pdf2 = file_get_contents($location);
        $this->assertNotEquals($pdf1, $pdf2, "Pdf content should change when modifying season dates.");
    } */
}
