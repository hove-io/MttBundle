README
======

What is MttBundle ?
-----------------------------

__Coming Soon__


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
