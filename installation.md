Installation
======

Requirements
------


#### I.Language

	php 5
####II.Extensions php5's
 
	php5-curl (curl package should be installed first)
	php5-intl
	php5-ggsql


####III.Database
 
	postgresql >= 9.0.0


####IV.project Navitia Mobility Manager
You need  install the project : https://github.com/CanalTP/navitia-mobility-manager


####V.RabbitMQ

link to install rabitmq: https://www.rabbitmq.com/download.html

####VI.PdfGenrator
follow this link if it's not  install in your machine.

https://github.com/CanalTP/pdfGenerator


####VII.AMQP Mtt </h4>

Clone this project in your machine and follow the intructions 

https://github.com/CanalTP/AmqpMttWorkers



Installation
---------------



You need composer to install the MttBundle.
####1 Configuration

Create Yaml file `config.media_manager.yml` in app/config and add this code below
```
canal_tp_media_manager:
    configurations:
        mtt:
            name: MTT
            storage:
                type: filesystem
                path:  #/tmp/pdf/
                url:  #storage Url 
            strategy: CanalTP\MediaManager\Strategy\DefaultStrategy

```	

Open your composer.json in your project and add :

	"canaltp/mtt-bridge-bundle": "0.3.2",
	"canaltp/mtt-bundle": "1.9.2"

you can find bundles in packagist: https://packagist.org/packages/canaltp/

Add bundle in your app/AppKernel.php

	new CanalTP\MttBridgeBundle\CanalTPMttBridgeBundle(),
 	new CanalTP\MttBundle\CanalTPMttBundle(),
	new CanalTP\MediaManagerBundle\CanalTPMediaManagerBundle(),

####2.Install PHP dependencies

	curl -sS https://getcomposer.org/installer | php

	composer.phar install --prefer-source




Contributing
-------------

1. Vincent Degroote - vincent.degroote@canaltp.fr
2. Rémy Abi-Khalil - remy.abikhalil@canaltp.fr
