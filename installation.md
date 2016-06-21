Installation
======

Requirements
------


#### I. Language

	php 5
#### II. php5 Extensions
 
	php5-curl (curl package should be installed first)
	php5-intl
	php5-pgsql


####III. Database
 
	postgresql >= 9.0.0


#### IV.  Navitia Mobility Manager project
You need to install the project : https://github.com/CanalTP/navitia-mobility-manager


#### V. RabbitMQ

link to install rabbitmq: https://www.rabbitmq.com/download.html

#### VI. PdfGenrator
follow this link if it's not install in your machine.

https://github.com/CanalTP/pdfGenerator


#### VII. AMQP Mtt Worker

Clone this project in your machine and follow the intructions 

https://github.com/CanalTP/AmqpMttWorkers



Installation
---------------

#### I. Clone the repository

`````	
git clone git@github.com:CanalTP/MttBundle.git
`````



You need composer to install the MttBundle.
#### II. Configuration

Create Yaml file `config.media_manager.yml` in app/config and add this code below
```yaml
canal_tp_media_manager:
    configurations:
        mtt:
            name: MTT
            storage:
                type: filesystem
                path:  #/tmp/pdf/ this is a folder to store pdf files
                url:  # url where it's possbile to access the path from a browser           
            strategy: CanalTP\MediaManager\Strategy\DefaultStrategy
```	

Open your composer.json in your project and add :

```json
"canaltp/mtt-bridge-bundle": "0.3.2",
"canaltp/mtt-bundle": "1.9.2"
```

you can find bundles in packagist: https://packagist.org/packages/canaltp/

Add bundle in your app/AppKernel.php

````php
new CanalTP\MttBridgeBundle\CanalTPMttBridgeBundle(),
new CanalTP\MttBundle\CanalTPMttBundle(),
new CanalTP\MediaManagerBundle\CanalTPMediaManagerBundle(),
````

You should add some configuration in `config.yml`
 "import"  section

```yaml
import:
    -{ resource: config.media_manager.yml}
```

"assetic" section

```yaml
assetic:
    bundles:
        - CanalTPMttBundle
```
"braincrafted_bootstrap" section

```yaml
customize:
    bootstrap_template: CanalTPMttBundle:Bootstrap:bootstrap.less.twig

```
in "docrtrine -->orm-->entity_manager-->default-->mapping" section

````yaml
doctrine:
    orm:
        entity_manager:
            default:
               mapping:
                    CanalTPMttBundle: ~
````
####You need to add some configurations to generate  time card in pdf 
in your symfony project in `parameters.yml.dist` you should add 

````yaml
    pdf_generator_url: 'path/pdfGenerator/web'
    canal_tp_mtt.amqp_server_host: localhost
    canal_tp_mtt.amqp_server_user: guest
    canal_tp_mtt.amqp_server_pass: guest
    canal_tp_mtt.amqp_server_vhost: /
    canal_tp_mtt.amqp_server_port: '5672'
````

Launch acknowledge worker from navitia-mobility-manager console 

`app/console mtt:amqp:waitForAcks`

add '&' to get background task

`
app/console mtt:amqp:waitForAcks &
`
	

#### III. Install PHP dependencies

	curl -sS https://getcomposer.org/installer | php

	composer.phar install --prefer-source

in terminal run this command below to initialize your database
````
php app/console sam:database:reset
````

Install asset,translation,rounting
````
php app/console assets:install --symlink
php app/console braincrafted:bootstrap:generate
php app/console assetic:dump
php app/console bazinga:js-translation:dump
php app/console fos:js-routing:dump
````
