<?php

namespace CanalTP\MttBundle\Tests\Unit\Services;

use CanalTP\NmmPortalBundle\Entity\Perimeter;
use CanalTP\MttBundle\Entity\Season;
use CanalTP\MttBundle\Services\CalendarManager;

class CalendarManagerTest extends \PHPUnit_Framework_TestCase
{
    const COVERAGE = 'jdr';
    const CALENDAR_ID = 'XYZ';

    protected static $routes;
    protected static $calendars;
    protected static $datetimes;

    public function setUp()
    {
        $perimeterEntity = new Perimeter();
        $perimeterEntity->setExternalCoverageId(self::COVERAGE);
        $this->seasonEntity = new Season();
        $this->seasonEntity->setPerimeter($perimeterEntity);
    }

    public static function setUpBeforeClass()
    {
        self::$datetimes = array(
            0 => array(
                array(
                    "043500",
                    "062700"
                ),
                array(
                    "124000",
                    "143400"
                ),
                array(
                    "222700",
                    "002000"
                )
            ),
            1 => array(
                array(
                    "",
                    "062700"
                ),
                array(
                    "124000",
                    ""
                ),
                array(
                    "222700",
                    "002000"
                )
            )
        );

        self::$routes = json_decode('
            {
                "routes": [
                    {
                        "direction": {},
                        "name": "route_1",
                        "id": "route:1",
                        "line": {}
                    }
                ]
            }
        ')->routes;

        self::$calendars = json_decode('
            {
                "calendars": [
                    {
                        "active_periods": [],
                        "week_pattern": {},
                        "name": "calendar_1",
                        "validity_pattern": {
                            "beginning_date": "20151104",
                            "days": ""
                        },
                        "id": "calendar:1"
                    }
                ]
            }
        ')->calendars;
    }

    public static function tearDownAfterClass()
    {
        self::$datetimes = null;
        self::$routes = null;
        self::$calendars = null;
    }

    public function testIsIncludedWithValidCalendar()
    {
        $this->seasonEntity->setStartDate(new \DateTime('2016-04-24'));
        $this->seasonEntity->setEndDate(new \DateTime('2016-05-01'));

        $navitiaServiceProphecy = $this->getNavitiaServiceProphecy();
        $translatorProphecy = $this->getTranslatorProphecy();

        $calendarManager = new CalendarManager($navitiaServiceProphecy->reveal(), $translatorProphecy->reveal());

        $this->assertTrue($calendarManager->isIncluded(self::CALENDAR_ID, $this->seasonEntity));
    }

    public function testIsIncludedWithCalendarOutOfSeason()
    {
        $this->seasonEntity->setStartDate(new \DateTime('2015-04-24'));
        $this->seasonEntity->setEndDate(new \DateTime('2015-05-01'));

        $navitiaServiceProphecy = $this->getNavitiaServiceProphecy();
        $translatorProphecy = $this->getTranslatorProphecy();

        $calendarManager = new CalendarManager($navitiaServiceProphecy->reveal(), $translatorProphecy->reveal());

        $this->assertFalse($calendarManager->isIncluded(self::CALENDAR_ID, $this->seasonEntity));
    }

    public function testIsIncludedWithUnknownCalendar()
    {
        $this->seasonEntity->setStartDate(new \DateTime('2016-04-24'));
        $this->seasonEntity->setEndDate(new \DateTime('2016-05-01'));

        $navitiaServiceProphecy = $this->prophesize('\CanalTP\MttBundle\Services\Navitia');
        $navitiaServiceProphecy
            ->getCalendar(self::COVERAGE, self::CALENDAR_ID)
            ->willThrow('\Navitia\Component\Exception\NotFound\UnknownObjectException');

        $translatorProphecy = $this->getTranslatorProphecy();

        $calendarManager = new CalendarManager($navitiaServiceProphecy->reveal(), $translatorProphecy->reveal());
        $this->assertFalse($calendarManager->isIncluded(self::CALENDAR_ID, $this->seasonEntity));
    }

    /**
     * Get Navitia Service
     *
     * @return \Prophecy\Prophecy\ObjectProphecy
     */
    private function getNavitiaServiceProphecy()
    {
        $calendarJson = <<<JSON
{
    "calendars": [
        {
            "active_periods": [
                {
                    "begin": "20160425",
                    "end": "20160430"
                }
            ]
        }
    ]
}
JSON;

        $navitiaServiceProphecy = $this->prophesize('\CanalTP\MttBundle\Services\Navitia');
        $navitiaServiceProphecy
            ->getCalendar(self::COVERAGE, self::CALENDAR_ID)
            ->willReturn(json_decode($calendarJson));

        return $navitiaServiceProphecy;
    }

    /**
     * Get Translator
     *
     * @return \Prophecy\Prophecy\ObjectProphecy
     */
    private function getTranslatorProphecy()
    {
        return $this->prophesize('\Symfony\Component\Translation\TranslatorInterface');
    }

    public function testGetCalendarsForLine()
    {
        $translator = $this->getMockBuilder('Symfony\Component\Translation\Translator')
            ->disableOriginalConstructor()
            ->getMock();

        $navitia = $this->getMockBuilder('CanalTP\MttBundle\Services\Navitia')
            ->disableOriginalConstructor()
            ->getMock();

        $navitia->expects($this->any())
            ->method('getLineRoutes')
            ->will($this->returnValue(self::$routes));

        $navitia->expects($this->any())
            ->method('getLineCalendars')
            ->will($this->returnValue(self::$calendars));

        $routeSchedules = json_decode('
        {
            "notes": [
                {
                    "type": "notes",
                    "id": "note:1",
                    "value": "note on line 1"
                },
                {
                    "type": "notes",
                    "id": "destination:1",
                    "value": "destination is foo"
                },
                {
                    "type": "notes",
                    "id": "note:2",
                    "value": "note on vehicle_journey 1"
                }
            ],
            "exceptions": [],
            "route_schedules": [
                {
                    "table": {
                        "headers": [
                            {
                                "display_informations": {},
                                "additional_informations": [
                                    "has_datetime_estimated"
                                ],
                                "links": [
                                    {
                                        "type": "line",
                                        "id": "line:1"
                                    },
                                    {
                                        "type": "vehicle_journey",
                                        "id": "vehicle_journey:1_dst_1"
                                    },
                                    {
                                        "type": "route",
                                        "id": "route:1"
                                    },
                                    {
                                        "type": "commercial_mode",
                                        "id": "commercial_mode:3"
                                    },
                                    {
                                        "type": "physical_mode",
                                        "id": "physical_mode:3"
                                    },
                                    {
                                        "type": "network",
                                        "id": "network:1"
                                    },
                                    {
                                        "internal": true,
                                        "type": "notes",
                                        "id": "note:1"
                                    }
                                ]
                            }
                        ],
                        "rows": [
                            {
                                "stop_point": {
                                    "codes": [
                                        {
                                            "type": "external_code",
                                            "value": "1"
                                        }
                                    ],
                                    "name": "stop 1",
                                    "links": [],
                                    "coords": {},
                                    "label": "stop 1",
                                    "id": "stop_point:1",
                                    "stop_area": {}
                                },
                                "date_times": [
                                    {
                                        "date_time": "'.self::$datetimes[0][0][0].'",
                                        "additional_informations": [],
                                        "links": [
                                            {
                                                "internal": true,
                                                "type": "notes",
                                                "id": "note:2",
                                                "rel": "notes"
                                            },
                                            {
                                                "type": "vehicle_journey",
                                                "value": "vehicle_journey:1_dst_1",
                                                "rel": "vehicle_journeys",
                                                "id": "vehicle_journey:1_dst_1"
                                            }
                                        ]
                                    },
                                    {
                                        "date_time": "'.self::$datetimes[0][0][1].'",
                                        "additional_informations": [],
                                        "links": [
                                            {
                                                "internal": true,
                                                "type": "notes",
                                                "id": "destination:1",
                                                "rel": "notes"
                                            },
                                            {
                                                "type": "vehicle_journey",
                                                "value": "vehicle_journey:2_dst_1",
                                                "rel": "vehicle_journeys",
                                                "id": "vehicle_journey:2_dst_1"
                                            }
                                        ]
                                    }
                                ]
                            },
                            {
                                "stop_point": {
                                    "codes": [
                                        {
                                            "type": "external_code",
                                            "value": "2"
                                        }
                                    ],
                                    "name": "stop 2",
                                    "links": [],
                                    "coords": {},
                                    "label": "stop 2",
                                    "id": "stop_point:2",
                                    "stop_area": {}
                                },
                                "date_times": [
                                    {
                                        "date_time": "'.self::$datetimes[0][1][0].'",
                                        "additional_informations": [],
                                        "links": [
                                            {
                                                "internal": true,
                                                "type": "notes",
                                                "id": "note:2",
                                                "rel": "notes"
                                            },
                                            {
                                                "type": "vehicle_journey",
                                                "value": "vehicle_journey:1_dst_1",
                                                "rel": "vehicle_journeys",
                                                "id": "vehicle_journey:1_dst_1"
                                            }
                                        ]
                                    },
                                    {
                                        "date_time": "'.self::$datetimes[0][1][1].'",
                                        "additional_informations": [],
                                        "links": [
                                            {
                                                "internal": true,
                                                "type": "notes",
                                                "id": "destination:1",
                                                "rel": "notes"
                                            },
                                            {
                                                "type": "vehicle_journey",
                                                "value": "vehicle_journey:2_dst_1",
                                                "rel": "vehicle_journeys",
                                                "id": "vehicle_journey:2_dst_1"
                                            }
                                        ]
                                    }
                                ]
                            },
                            {
                                "stop_point": {
                                    "codes": [
                                        {
                                            "type": "external_code",
                                            "value": "3"
                                        }
                                    ],
                                    "name": "stop 3",
                                    "links": [],
                                    "coords": {},
                                    "label": "stop 3",
                                    "id": "stop_point:3",
                                    "stop_area": {}
                                },
                                "date_times": [
                                    {
                                        "date_time": "'.self::$datetimes[0][2][0].'",
                                        "additional_informations": [],
                                        "links": [
                                            {
                                                "internal": true,
                                                "type": "notes",
                                                "id": "note:2",
                                                "rel": "notes"
                                            },
                                            {
                                                "type": "vehicle_journey",
                                                "value": "vehicle_journey:1_dst_1",
                                                "rel": "vehicle_journeys",
                                                "id": "vehicle_journey:1_dst_1"
                                            }
                                        ]
                                    },
                                    {
                                        "date_time": "'.self::$datetimes[0][2][1].'",
                                        "additional_informations": [],
                                        "links": [
                                            {
                                                "internal": true,
                                                "type": "notes",
                                                "id": "destination:1",
                                                "rel": "notes"
                                            },
                                            {
                                                "type": "vehicle_journey",
                                                "value": "vehicle_journey:2_dst_1",
                                                "rel": "vehicle_journeys",
                                                "id": "vehicle_journey:2_dst_1"
                                            }
                                        ]
                                    }
                                ]
                            }
                        ]
                    },
                    "links": [
                        {
                            "type": "notes",
                            "id": "note:1"
                        }
                    ]
                }
            ]
        }');

        $navitia->expects($this->once())
            ->method('getRouteSchedulesByRouteAndCalendar')
            ->will($this->returnValue($routeSchedules));

        $calendarManager = new CalendarManager($navitia, $translator);

        $stoptimes = array();
        foreach (self::$datetimes[0] as $index => $datetimeSet) {
            $previousDatetime = null;
            $dayOffset = false;
            foreach ($datetimeSet as $datetime) {
                if (empty($datetime)) {
                    $stoptimes[$index][] = null;
                } else {
                    if ($previousDatetime && !$dayOffset && $previousDatetime > intVal($datetime)) {
                        $dayOffset = true;
                    }

                    $date = strtotime($datetime);
                    if ($dayOffset) {
                        $date = strtotime('+1 day', $date);
                    }

                    $stoptimes[$index][] = $date;
                    $previousDatetime = intVal($datetime);
                }
            }
        }

        $expected = json_decode('
            {
                "route:1": {
                    "direction": "route_1",
                    "calendars": {
                        "calendar:1": {
                            "route_schedules": {
                                "columns": 2,
                                "stops": [
                                    {
                                        "stopName": "stop 1",
                                        "stopExternalId": "stop_point:1",
                                        "stopTimes": [
                                            '.$stoptimes[0][0].',
                                            '.$stoptimes[0][1].'
                                        ]
                                    },
                                    {
                                        "stopName": "stop 2",
                                        "stopExternalId": "stop_point:2",
                                        "stopTimes": [
                                            '.$stoptimes[1][0].',
                                            '.$stoptimes[1][1].'
                                        ]
                                    },
                                    {
                                        "stopName": "stop 3",
                                        "stopExternalId": "stop_point:3",
                                        "stopTimes": [
                                            '.$stoptimes[2][0].',
                                            '.$stoptimes[2][1].'
                                        ]
                                    }
                                ],
                                "metadata": [
                                    {
                                        "type": "trip",
                                        "firstHour": '.$stoptimes[0][0].',
                                        "lastHour": '.$stoptimes[2][0].',
                                        "trip": "vehicle_journey:1_dst_1",
                                        "departureStop": "stop 1",
                                        "arrivalStop": "stop 3"
                                    },
                                    {
                                        "type": "trip",
                                        "firstHour": '.$stoptimes[0][1].',
                                        "lastHour": '.$stoptimes[2][1].',
                                        "trip": "vehicle_journey:2_dst_1",
                                        "departureStop": "stop 1",
                                        "arrivalStop": "stop 3"
                                    }
                                ]
                            },
                            "notes": [
                                {
                                    "type": "notes",
                                    "id": "note:1",
                                    "value": "note on line 1"
                                },
                                {
                                    "type": "notes",
                                    "id": "destination:1",
                                    "value": "destination is foo"
                                },
                                {
                                    "type": "notes",
                                    "id": "note:2",
                                    "value": "note on vehicle_journey 1"
                                }
                            ],
                            "name": "calendar_1",
                            "id": "calendar:1"
                        }
                    }
                }
            }
        ', true);

        $result = $calendarManager->getCalendarsForLine('coverage:1', 'network:1', 'line:1');

        $this->assertEquals(json_decode(json_encode($result), true), $expected);
    }

    public function testGetCalendarsForLineWithEmptyHours()
    {
        $translator = $this->getMockBuilder('Symfony\Component\Translation\Translator')
            ->disableOriginalConstructor()
            ->getMock();

        $navitia = $this->getMockBuilder('CanalTP\MttBundle\Services\Navitia')
            ->disableOriginalConstructor()
            ->getMock();

        $navitia->expects($this->any())
            ->method('getLineRoutes')
            ->will($this->returnValue(self::$routes));

        $navitia->expects($this->any())
            ->method('getLineCalendars')
            ->will($this->returnValue(self::$calendars));

        $routeSchedules = json_decode('
        {
            "notes": [
                {
                    "type": "notes",
                    "id": "note:1",
                    "value": "note on line 1"
                },
                {
                    "type": "notes",
                    "id": "destination:1",
                    "value": "destination is foo"
                },
                {
                    "type": "notes",
                    "id": "note:2",
                    "value": "note on vehicle_journey 1"
                }
            ],
            "exceptions": [],
            "route_schedules": [
                {
                    "table": {
                        "headers": [
                            {
                                "display_informations": {},
                                "additional_informations": [
                                    "has_datetime_estimated"
                                ],
                                "links": [
                                    {
                                        "type": "line",
                                        "id": "line:1"
                                    },
                                    {
                                        "type": "vehicle_journey",
                                        "id": "vehicle_journey:1_dst_1"
                                    },
                                    {
                                        "type": "route",
                                        "id": "route:1"
                                    },
                                    {
                                        "type": "commercial_mode",
                                        "id": "commercial_mode:3"
                                    },
                                    {
                                        "type": "physical_mode",
                                        "id": "physical_mode:3"
                                    },
                                    {
                                        "type": "network",
                                        "id": "network:1"
                                    },
                                    {
                                        "internal": true,
                                        "type": "notes",
                                        "id": "note:1"
                                    }
                                ]
                            }
                        ],
                        "rows": [
                            {
                                "stop_point": {
                                    "codes": [
                                        {
                                            "type": "external_code",
                                            "value": "1"
                                        }
                                    ],
                                    "name": "stop 1",
                                    "links": [],
                                    "coords": {},
                                    "label": "stop 1",
                                    "id": "stop_point:1",
                                    "stop_area": {}
                                },
                                "date_times": [
                                    {
                                        "date_time": "'.self::$datetimes[1][0][0].'",
                                        "additional_informations": [],
                                        "links": [
                                            {
                                                "internal": true,
                                                "type": "notes",
                                                "id": "note:2",
                                                "rel": "notes"
                                            },
                                            {
                                                "type": "vehicle_journey",
                                                "value": "vehicle_journey:1_dst_1",
                                                "rel": "vehicle_journeys",
                                                "id": "vehicle_journey:1_dst_1"
                                            }
                                        ]
                                    },
                                    {
                                        "date_time": "'.self::$datetimes[1][0][1].'",
                                        "additional_informations": [],
                                        "links": [
                                            {
                                                "internal": true,
                                                "type": "notes",
                                                "id": "destination:1",
                                                "rel": "notes"
                                            },
                                            {
                                                "type": "vehicle_journey",
                                                "value": "vehicle_journey:2_dst_1",
                                                "rel": "vehicle_journeys",
                                                "id": "vehicle_journey:2_dst_1"
                                            }
                                        ]
                                    }
                                ]
                            },
                            {
                                "stop_point": {
                                    "codes": [
                                        {
                                            "type": "external_code",
                                            "value": "2"
                                        }
                                    ],
                                    "name": "stop 2",
                                    "links": [],
                                    "coords": {},
                                    "label": "stop 2",
                                    "id": "stop_point:2",
                                    "stop_area": {}
                                },
                                "date_times": [
                                    {
                                        "date_time": "'.self::$datetimes[1][1][0].'",
                                        "additional_informations": [],
                                        "links": [
                                            {
                                                "internal": true,
                                                "type": "notes",
                                                "id": "note:2",
                                                "rel": "notes"
                                            },
                                            {
                                                "type": "vehicle_journey",
                                                "value": "vehicle_journey:1_dst_1",
                                                "rel": "vehicle_journeys",
                                                "id": "vehicle_journey:1_dst_1"
                                            }
                                        ]
                                    },
                                    {
                                        "date_time": "'.self::$datetimes[1][1][1].'",
                                        "additional_informations": [],
                                        "links": [
                                            {
                                                "internal": true,
                                                "type": "notes",
                                                "id": "destination:1",
                                                "rel": "notes"
                                            },
                                            {
                                                "type": "vehicle_journey",
                                                "value": "vehicle_journey:2_dst_1",
                                                "rel": "vehicle_journeys",
                                                "id": "vehicle_journey:2_dst_1"
                                            }
                                        ]
                                    }
                                ]
                            },
                            {
                                "stop_point": {
                                    "codes": [
                                        {
                                            "type": "external_code",
                                            "value": "3"
                                        }
                                    ],
                                    "name": "stop 3",
                                    "links": [],
                                    "coords": {},
                                    "label": "stop 3",
                                    "id": "stop_point:3",
                                    "stop_area": {}
                                },
                                "date_times": [
                                    {
                                        "date_time": "'.self::$datetimes[1][2][0].'",
                                        "additional_informations": [],
                                        "links": [
                                            {
                                                "internal": true,
                                                "type": "notes",
                                                "id": "note:2",
                                                "rel": "notes"
                                            },
                                            {
                                                "type": "vehicle_journey",
                                                "value": "vehicle_journey:1_dst_1",
                                                "rel": "vehicle_journeys",
                                                "id": "vehicle_journey:1_dst_1"
                                            }
                                        ]
                                    },
                                    {
                                        "date_time": "'.self::$datetimes[1][2][1].'",
                                        "additional_informations": [],
                                        "links": [
                                            {
                                                "internal": true,
                                                "type": "notes",
                                                "id": "destination:1",
                                                "rel": "notes"
                                            },
                                            {
                                                "type": "vehicle_journey",
                                                "value": "vehicle_journey:2_dst_1",
                                                "rel": "vehicle_journeys",
                                                "id": "vehicle_journey:2_dst_1"
                                            }
                                        ]
                                    }
                                ]
                            }
                        ]
                    },
                    "links": [
                        {
                            "type": "notes",
                            "id": "note:1"
                        }
                    ]
                }
            ]
        }');

        $navitia->expects($this->once())
            ->method('getRouteSchedulesByRouteAndCalendar')
            ->will($this->returnValue($routeSchedules));

        $calendarManager = new CalendarManager($navitia, $translator);

        $stoptimes = array();
        foreach (self::$datetimes[1] as $index => $datetimeSet) {
            $previousDatetime = null;
            $dayOffset = false;
            foreach ($datetimeSet as $datetime) {
                if (empty($datetime)) {
                    $stoptimes[$index][] = null;
                } else {
                    if ($previousDatetime && !$dayOffset && $previousDatetime > intVal($datetime)) {
                        $dayOffset = true;
                    }

                    $date = strtotime($datetime);
                    if ($dayOffset) {
                        $date = strtotime('+1 day', $date);
                    }

                    $stoptimes[$index][] = $date;
                    $previousDatetime = intVal($datetime);
                }
            }
        }

        $expected = json_decode('
            {
                "route:1": {
                    "direction": "route_1",
                    "calendars": {
                        "calendar:1": {
                            "route_schedules": {
                                "columns": 2,
                                "stops": [
                                    {
                                        "stopName": "stop 1",
                                        "stopExternalId": "stop_point:1",
                                        "stopTimes": [
                                            null,
                                            '.$stoptimes[0][1].'
                                        ]
                                    },
                                    {
                                        "stopName": "stop 2",
                                        "stopExternalId": "stop_point:2",
                                        "stopTimes": [
                                            '.$stoptimes[1][0].',
                                            null
                                        ]
                                    },
                                    {
                                        "stopName": "stop 3",
                                        "stopExternalId": "stop_point:3",
                                        "stopTimes": [
                                            '.$stoptimes[2][0].',
                                            '.$stoptimes[2][1].'
                                        ]
                                    }
                                ],
                                "metadata": [
                                    {
                                        "type": "trip",
                                        "firstHour": '.$stoptimes[1][0].',
                                        "lastHour": '.$stoptimes[2][0].',
                                        "trip": "vehicle_journey:1_dst_1",
                                        "departureStop": "stop 2",
                                        "arrivalStop": "stop 3"
                                    },
                                    {
                                        "type": "trip",
                                        "firstHour": '.$stoptimes[0][1].',
                                        "lastHour": '.$stoptimes[2][1].',
                                        "trip": "vehicle_journey:2_dst_1",
                                        "departureStop": "stop 1",
                                        "arrivalStop": "stop 3"
                                    }
                                ]
                            },
                            "notes": [
                                {
                                    "type": "notes",
                                    "id": "note:1",
                                    "value": "note on line 1"
                                },
                                {
                                    "type": "notes",
                                    "id": "destination:1",
                                    "value": "destination is foo"
                                },
                                {
                                    "type": "notes",
                                    "id": "note:2",
                                    "value": "note on vehicle_journey 1"
                                }
                            ],
                            "name": "calendar_1",
                            "id": "calendar:1"
                        }
                    }
                }
            }
        ', true);

        $result = $calendarManager->getCalendarsForLine('coverage:1', 'network:1', 'line:1');

        $this->assertEquals(json_decode(json_encode($result), true), $expected);
    }

    /**
     * @expectedException \Exception
     */
    public function testGetCalendarsForLineWithException()
    {
        $translator = $this->getMockBuilder('Symfony\Component\Translation\Translator')
            ->disableOriginalConstructor()
            ->getMock();

        $navitia = $this->getMockBuilder('CanalTP\MttBundle\Services\Navitia')
            ->disableOriginalConstructor()
            ->getMock();

        $navitia->expects($this->any())
            ->method('getLineRoutes')
            ->will($this->returnValue(self::$routes));

        $navitia->expects($this->any())
            ->method('getLineCalendars')
            ->will($this->returnValue(self::$calendars));

        $routeSchedules = json_decode('
        {
            "notes": [
                {
                    "type": "notes",
                    "id": "note:1",
                    "value": "note on line 1"
                },
                {
                    "type": "notes",
                    "id": "destination:1",
                    "value": "destination is foo"
                },
                {
                    "type": "notes",
                    "id": "note:2",
                    "value": "note on vehicle_journey 1"
                }
            ],
            "exceptions": [],
            "route_schedules": [
                {
                    "table": {
                        "headers": [
                            {
                                "display_informations": {},
                                "additional_informations": [
                                    "has_datetime_estimated"
                                ],
                                "links": [
                                    {
                                        "type": "line",
                                        "id": "line:1"
                                    },
                                    {
                                        "type": "vehicle_journey",
                                        "id": "vehicle_journey:1_dst_1"
                                    },
                                    {
                                        "type": "route",
                                        "id": "route:1"
                                    },
                                    {
                                        "type": "commercial_mode",
                                        "id": "commercial_mode:3"
                                    },
                                    {
                                        "type": "physical_mode",
                                        "id": "physical_mode:3"
                                    },
                                    {
                                        "type": "network",
                                        "id": "network:1"
                                    },
                                    {
                                        "internal": true,
                                        "type": "notes",
                                        "id": "note:1"
                                    }
                                ]
                            }
                        ],
                        "rows": []
                    },
                    "links": [
                        {
                            "type": "notes",
                            "id": "note:1"
                        }
                    ]
                }
            ]
        }');

        $navitia->expects($this->once())
            ->method('getRouteSchedulesByRouteAndCalendar')
            ->will($this->returnValue($routeSchedules));

        $calendarManager = new CalendarManager($navitia, $translator);

        $calendarManager->getCalendarsForLine('coverage:1', 'network:1', 'line:1');
    }
}
