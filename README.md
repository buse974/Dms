DMS
===

Document management system for ZF2


# Description

The DMS lets you manage your documents for [Zend Framework 2](http://framework.zend.com/).

# Installation

## Prequisites :

Before installing DMS module, please be sure to have installed on your apache server, the following PHP depencies :
- libreoffice-headers
- uniconv



## With composer (recommended) :

Composer helps manage dependencies for PHP projects. Find more info here: <http://getcomposer.org>

Add this package (`buse974/dms`) to your `composer.json` file, or just run the following at the
command line:

```
$ composer require buse974/dms
```

## By cloning project :

Install the [DMS] (https://github.com/buse974/Dms.git) module by cloning it into `./vendor/` directory.

## Post-installation

This package follows the [PSR-0](http://www.php-fig.org/psr/psr-0/) autoloading standard. If you are
using composer to install, you just require the generated autoloader:

```php
require "<projectpath>/vendor/autoload.php";
```

### Application config file
Enabling it in your `application.config.php` file.
```
<?php
return array(
    'modules' => array(
        'Dms',
    ),
    'module_paths' => array(
       'Dms' => __DIR__ . '/../vendor/buse974/dms',
    ),
),
);
```
### Local file config

Copy and paste the following configuration in your `config/autoloader/local.php`. You can find these configuration in `<application_path>/vendor/buse974/dms/config/local.php.dist`
```
'dms-conf' => array(
    /*
     * Allowed sizes - You can add as many as you need
     */
    'size_allowed' => array(                    
                array('width' => 300, 'height' => 200),
                array('width' => 300, 'height' => 300),
                array('width' => 150, 'height' => 100),
                array('width' => 80, 'height' => 80),
                array('width' => 300),
        ),
        /*
         * path where all the documents will be uploaded
         */
        'default_path' => 'upload/',      
        /*
         * http adapter - You have to add your [http adapter](#HTTP-adapter) 
         * in your config/autoloader/local.php or config/autoloader/global.php
         * If you already have a http adapter, please just specify its name
         */
        'adapter' => 'http-adapter',             
        'convert' => array(
                /* 
                 * path where you want to convert and stock the temporaries files
                 * !! apache need all right for writing and reading on this directory !!
                 */
                'tmp' => '/tmp/',    
        ),
        /*
         * the headers to add to your files - especially for cross-domain
         */
        'headers'=> array(                        
                'Access-Control-Allow-Origin'=>'http://local.wow.in',
                'Access-Control-Allow-Credentials'=>'true'
        ),
),
```
### HTTP adapter (if it is not existing yet)
```
'http-adapter' => array(
        'adapter' => 'Zend\Http\Client\Adapter\Socket',
        'maxredirects'   => 5,
        'timeout'        => 10,
        'sslverifypeer' => false,
),
```

# Usage

## By DMS service

### Add a document

```php
$manager = $this->serviceManager->get('dms.service');

/*
 *  exemple
 */
$image = file_get_contents(__DIR__ . '/../../_file/gnu.png');

/*
 * string - document's codings specified in `dma/coding/CodingInterface`
 */
$document['coding'] = 'base';  

/*
 * mime-type
 */
$document['type'] = 'image/png';  

/*
 * datas with specified encoding
 * if you choose url coding, you can add the picture url which will be upload and save
 */
$document['data'] =  base64_encode($image);        

/*
 * Return the document's token
 */
$token = $manager->add($document);   
```

To access this file use this url http://\<dns\>/data/\<token\> .

### Resize a document

Following the previous example

```php
/*
 *  width x height in a string - Return a new token for the document resized
 */
$new_token = $manager->resize('80x80');    
```

To access this file use this url http://\<dns\>/data/\<new_token\> .

## By DMS manager


### Add and get a document

```php

$manager = $this->serviceManager->get('dms.manager');

/*
 * file exemple
 */
$image = 'file_get_contents(__DIR__ . '/../../_file/gnu.png');

$document = new Dms\Document\Document();
$document->setDatas($image); 
$document->setFormat('png');            


$manager->loadDocument($document);    
$manager->setFormat('jpg');
$document->setSize('80x80'); 
$manager->writeFile();

/*
 * Get the document's token (unique id)
 */
$document = $manager->getDocument();

/*
 * Return the document's token
 */
$document->getId();
```
