<?php

namespace CanalTP\MttBundle\Tests\Functional\Controller;

class CalendarControllerTest extends AbstractControllerTest
{
    private function getViewRoute()
    {
        return $this->generateRoute(
            'canal_tp_mtt_calendar_view',
            // fake params since we mock navitia
            array(
                'externalNetworkId' => 'network:Filbleu',
                'externalRouteId' => 'test',
                'externalStopPointId' => 'test'
            )
        );
    }

    public function setUp()
    {
        parent::setUp();
        $this->setService('canal_tp_mtt.navitia', $this->getMockedNavitia());
    }

    public function testCalendarsPresentViewAction()
    {
        $crawler = $this->doRequestRoute($this->getViewRoute());

        $this->assertTrue($crawler->filter('h3')->count() == 1, 'Expected h3 title.');
        $this->assertTrue($crawler->filter('.nav.nav-tabs > li')->count() == 4, 'Expected 4 calendars. Found ' . $crawler->filter('.nav.nav-tabs > li')->count());
    }

    public function testCalendarsNamesViewAction()
    {
        $crawler = $this->doRequestRoute($this->getViewRoute());
        // comes from the stub
        $calendarsName = array('Semaine scolaire', 'Semaine hors scolaire', "Samedi", "Dimanche et fêtes");
        foreach ($calendarsName as $name) {
            $this->assertTrue(
                $crawler->filter('html:contains("' . $name . '")')->count() == 1,
                "Calendar $name not found in answer"
            );
        }
    }

    public function testHoursConsistencyViewAction()
    {
        $crawler = $this->doRequestRoute($this->getViewRoute());
        $nodeValues = $crawler->filter('.grid-time-column > div:first-child')->each(function ($node, $i) {
            return (int) substr($node->text(), 0, strlen($node->text() - 1));
        });
        foreach ($nodeValues as $value) {
            $this->assertTrue(
                is_numeric($value),
                'Hour not numeric found.'
            );
            $this->assertTrue(
                $value >= 0 && $value < 24,
                "Hour $value not in the range 0<->23."
            );
        }
    }

    public function testMinutesConsistencyViewAction()
    {
        $crawler = $this->doRequestRoute($this->getViewRoute());
        $nodeValues = $crawler->filter('.grid-time-column > div:not(:first-child)')->each(function ($node, $i) {
            $count = preg_match('/^([\d]+)/', $node->text(), $matches);
            if ($count == 1) {
                return (int) $matches[0];
            } else {
                return false;
            }
        });
        foreach ($nodeValues as $value) {
            $this->assertTrue(
                is_numeric($value),
                'Minute not numeric found.'
            );
            $this->assertTrue(
                $value >= 0 && $value < 60,
                "Minute $value not in the range 0<->59."
            );
        }
    }

    public function testExceptionsViewAction()
    {
        $crawler = $this->doRequestRoute($this->getViewRoute());

        $this->assertTrue(
            $crawler->filter('html:contains("Sauf le 09/05/2014")')->count() > 0,
            "the exception value was not found in html."
        );
        $this->assertTrue(
            $crawler->filter('html:contains("Y compris le 09/05/2014")')->count() > 0,
            "the exception value was not found in html."
        );
    }

    public function testFootNotesConsistencyViewAction()
    {
        $crawler = $this->doRequestRoute($this->getViewRoute());

        $this->assertTrue(
            $crawler->filter('html:contains("au plus tard la veille du déplacement du lundi au vendredi de 9h à 12h30 et de 13h30 à 16h30.")')->count() > 0,
            "the note value was not found in html."
        );

        $this->assertTrue(
            $crawler->filter(
                'html:contains("au plus tard la veille du déplacement du lundi au vendredi de 9h à 12h30 et de 13h30 à 16h30.")')->count() == 1,
                "the note value was found in html more than once."
        );

        $this->assertTrue(
            $crawler->filter(
                '.tab-content > .tab-pane:first-child .notes-wrapper > div:not(:first-child)')->count() == 4,
                "Expected 4 notes label, found " . $crawler->filter('.tab-content > .tab-pane:first-child .notes-wrapper > div:not(:first-child)')->count()
        );

        $notesLabels = $crawler
            ->filter(
                '.tab-content > .tab-pane:first-child .notes-wrapper > div:not(:first-child) > span.bold'
            )->each(function ($node, $i) {
                return $node->text();
            });

        $asciiStart = 97;
        foreach ($notesLabels as $label) {
            $this->assertTrue(ord($label) == $asciiStart, "Note label $label should be " . chr($asciiStart));
            $asciiStart++;
        }
        // check if we find consistent note in timegrid
        $notes = $crawler->filter('.grid-time-column > div:not(:first-child)')->each(function ($node, $i) {
            $count = preg_match('/^[\d]+([a-z]{1})/', $node->text(), $matches);
            if ($count == 1) {
                return $matches[1];
            }
        });

        foreach ($notes as $note) {
            if (!empty($note)) {
                $this->assertTrue(in_array($note, $notesLabels), "Found note label $note in timegrid not present in notes wrapper.");
            }
        }
    }

    public function testStopPointCodeBlock()
    {
        $translator = $this->client->getContainer()->get('translator');
        $season = $this->getRepository('CanalTPMttBundle:Season')->find(1);

        $crawler = $this->doRequestRoute($this->getViewRoute());
        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("MERMB-1")')->count(),
            "Stop point code (external code) not found in stop point timetable view page"
        );
    }
}
