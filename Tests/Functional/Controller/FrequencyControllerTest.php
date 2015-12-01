<?php

namespace CanalTP\MttBundle\Tests\Functional\Controller;

use CanalTP\MttBundle\Tests\DataFixtures\ORM\Fixture;

class FrequencyControllerTest extends AbstractControllerTest
{
    private function getViewRoute($block, $externalStopPointId = Fixture::EXTERNAL_STOP_POINT_ID)
    {
        return $this->generateRoute(
            'canal_tp_mtt_stop_timetable_view',
            array(
                'externalNetworkId' => $block->getStopTimetable()->getLineConfig()->getSeason()->getPerimeter()->getExternalNetworkId(),
                'externalLineId' 	=> $block->getStopTimetable()->getLineConfig()->getExternalLineId(),
                'externalRouteId' 	=> $block->getStopTimetable()->getExternalRouteId(),
                'seasonId' 			=> $block->getStopTimetable()->getLineConfig()->getSeason()->getId(),
                'externalStopPointId' => $externalStopPointId
            )
        );
    }

    private function getFormRoute($block)
    {
        return $this->generateRoute(
            'canal_tp_mtt_frequency_edit',
            array(
                'externalNetworkId' => $block->getStopTimetable()->getLineConfig()->getSeason()->getPerimeter()->getExternalNetworkId(),
                'blockId'           => $block->getId(),
                'layoutId'          => $block->getStopTimetable()->getLineConfig()->getLayoutConfig()->getId(),
            )
        );
    }

    public function testEditForm()
    {
        $block = $this->getRepository('CanalTPMttBundle:Block')->find(1);
        $route = $this->getFormRoute($block);
        $content = 'I\'m a poor lonesome cowboy, I\'ve a long long way from home And this poor lonesome cowboy....';
        $crawler = $this->client->request('GET', $route);
        $form = $crawler->selectButton('Enregistrer')->form();
        // set some values
        $form['block_frequencies_coll[frequencies][0][startTime][hour]'] = '10';
        $form['block_frequencies_coll[frequencies][0][endTime][hour]'] = '12';
        $form['block_frequencies_coll[frequencies][0][content]'] = $content;
        // submit the form
        $crawler = $this->client->submit($form);
        $crawler = $this->client->request('GET', $this->getViewRoute($block));
        $this->assertTrue($crawler->filter('html:contains("' . $content . '")')->count() > 0);
    }
}
