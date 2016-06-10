README
======

What is MttBundle ?
-----------------------------
Timetable application's main goal is to generate timetable files which can be found at bus stops or other public transportation stop points.

Requirements
------

 <h4>I.Language</h4>

	php 5
<h4>II.Extensions php5's</h4>
 
	php5-curl (curl package should be installed first)
	php5-intl
	php5-ggsql


<h4>III.Database</h4>
 
	postgresql >= 9.0.0

<h4>IV.RabbitMQ</h4>

link to install rabiitmq: https://www.rabbitmq.com/download.html

<h4>V.PdfGenrator</h4>
follow this link if it's not  install in your machine.

https://github.com/CanalTP/pdfGenerator


<h4>VI.AMQP Mtt </h4>

Clone this project in your machine and follow the intructions 

https://github.com/CanalTP/AmqpMttWorkers


Installation
-------------

You need composer to install the MethBundle.

1. Open your composer.json in your project
2. Add bundle in your app/AppKernel.php
3. Add require "canaltp/meth-bundle": "dev-master"
4. Add url of the repository, 'http://packagist.canaltp.fr'
5. Add configuration in your app/config/routing.yml __(not required)__
6. In your project don't forget to declare service of navitia component (in service.yml) /!\

    // AppKernel.php
    ...
    new CanalTP\MttBundle\CanalTPMethBundle(),
    ...

    // routing.yml
    canal_tp_meth:
        resource: "@CanalTPMethBundle/Resources/config/routing.yml"
        prefix:   /meth

    // composer.json
    {
        ...
        "repositories": [
            {
                "type": "composer",
                "url": "http://packagist.canaltp.fr"
            },
            ...
        ],
        "require": {
            ...
            "canaltp/mtt-bundle": "dev-master"
        },
        ...
    }

    // service.yml
    navitia_component.class: Navitia\Component\Service\ServiceFacade
    ...
    navitia_component:
        class:          "%navitia_component.class%"
        factory_class:  "%navitia_component.class%"
        factory_method: getInstance
        calls:
            - [ setConfiguration, [%config.navitia%]]

Contributing
-------------

1. Vincent Degroote - vincent.degroote@canaltp.fr
2. Rémy Abi-Khalil - remy.abikhalil@canaltp.fr
