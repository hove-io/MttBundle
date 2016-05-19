<?php

namespace CanalTP\MttBundle\Tests\Functional\Controller;

class FrequencyControllerTest extends AbstractControllerTest
{
    private function getViewRoute($block)
    {
        return $this->generateRoute(
            'canal_tp_mtt_timetable_view',
            array(
                'externalNetworkId' => $block->getTimetable()->getLineConfig()->getSeason()->getPerimeter()->getExternalNetworkId(),
                'externalLineId'    => $block->getTimetable()->getLineConfig()->getExternalLineId(),
                'externalRouteId'   => $block->getTimetable()->getExternalRouteId(),
                'seasonId'          => $block->getTimetable()->getLineConfig()->getSeason()->getId()
            )
        );
    }

    private function getFormRoute($block)
    {
        return $this->generateRoute(
            'canal_tp_mtt_frequency_edit',
            array(
                'externalNetworkId' => $block->getTimetable()->getLineConfig()->getSeason()->getPerimeter()->getExternalNetworkId(),
                'blockId'           => $block->getId(),
                'layoutId'          => $block->getTimetable()->getLineConfig()->getLayoutConfig()->getId(),
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
