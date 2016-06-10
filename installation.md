Installation
======

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


<h4>IV.project Navitia Mobility Manager</h4>
You need  install the project : https://github.com/CanalTP/navitia-mobility-manager


<h4>V.RabbitMQ</h4>

link to install rabitmq: https://www.rabbitmq.com/download.html

<h4>VI.PdfGenrator</h4>
follow this link if it's not  install in your machine.

https://github.com/CanalTP/pdfGenerator


<h4>VII.AMQP Mtt </h4>

Clone this project in your machine and follow the intructions 

https://github.com/CanalTP/AmqpMttWorkers



Installation
---------------



You need composer to install the MttBundle.
<h4>1 Configuration</h4>

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

<h4>2.Install PHP dependencies</h4>

	curl -sS https://getcomposer.org/installer | php

	composer.phar install --prefer-source




Contributing
-------------

1. Vincent Degroote - vincent.degroote@canaltp.fr
2. Rémy Abi-Khalil - remy.abikhalil@canaltp.fr
