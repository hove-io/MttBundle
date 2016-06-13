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
You need to install the project : https://github.com/CanalTP/navitia-mobility-manager


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

You shoul add some configuration in `config.yml`
 "import"  section

```
import:
      -{ resource: config.media_manager.yml}

```

"assetic" section

```
assetic:
	bundles:
	...
    	- CanalTPMttBundle
```
"braincrafted_bootstrap" section

```
customize:
	bootstrap_template: CanalTPMttBundle:Bootstrap:bootstrap.less.twig

```
in docrtrine -->orm-->entity_manager-->default-->mapping

````
doctrine:
	entity_manager:
		...
		default:
			...
			mapping:
				...
				CanalTPMttBundle: ~
				

````
in your symfony project in `parameter.yml.dist` you should add 

````
    pdf_generator_url: ''
    canal_tp_mtt.amqp_server_host: localhost
    canal_tp_mtt.amqp_server_user: guest
    canal_tp_mtt.amqp_server_pass: guest
    canal_tp_mtt.amqp_server_vhost: /
    canal_tp_mtt.amqp_server_port: '5672'

````

	

####2.Install PHP dependencies

	curl -sS https://getcomposer.org/installer | php

	composer.phar install --prefer-source

in terminal run this command below to initialize your database
````
php app/console sam:database:reset
````

Install asset,translation,rounting

	php app/console assets:install --symlink
	php app/console braincrafted:bootstrap:generate
	php app/console assetic:dump
	php app/console bazinga:js-translation:dump
	php app/console fos:js-routing:dump


Contributing
-------------

1. Vincent Degroote - vincent.degroote@canaltp.fr
2. Rémy Abi-Khalil - remy.abikhalil@canaltp.fr
