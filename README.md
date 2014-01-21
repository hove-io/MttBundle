README
======

What is MethBundle ?
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

    // AppKernel.php
    ...
    new CanalTP\MethBundle\CanalTPMethBundle(),
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
            "canaltp/meth-bundle": "dev-master"
        },
        ...
    }

Contributing
-------------

1. Vincent Degroote - vincent.degroote@canaltp.fr
2. Rémy Abi-Khalil - remy.abikhalil@canaltp.fr
