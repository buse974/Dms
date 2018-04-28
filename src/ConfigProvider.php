<?php

namespace Dms;

use Dms\Storage\Storage;
use Dms\Document\Manager;
use Dms\Service\DmsService;
use Dms\Coding\Url\Url;
use Dms\Resize\Resize;
use Zend\Diactoros\Response\JsonResponse;
use Dms\Document\NoFileException;
use Dms\Document\Document;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\TextResponse;
use Tests\FFMpeg\Unit\Format\Audio\AacTest;

/**
 * The configuration provider for the App module
 *
 * @see https://docs.zendframework.com/zend-component-installer/
 */
class ConfigProvider
{
    /**
     * Returns the configuration array
     *
     * To add a bit of a structure, each section is defined in a separate
     * method which returns an array with its configuration.
     *
     * @return array
     */
    public function __invoke() : array
    {
        return [
            'dependencies' => $this->getDependencies(),
            'routes'       => $this->getRoutes(),
            'dms-conf' => [
                'size_allowed' => [
                    ['width' => 300, 'height' => 200],
                    ['width' => 300, 'height' => 300],
                    ['width' => 150, 'height' => 100],
                    ['width' => 80, 'height' => 80],
                    ['width' => 300],
                ],
                'check_size_allowed' => false,
                'default_path' => 'upload/',
                'adapter' => 'http-adapter',
                'convert' => [
                    'tmp' => '/tmp/',
                ],
                'headers'=> [
                ],
               /* 'storage' => [
                    'name' => 's3',
                    'bucket' => 'NAME-BUCKET',
                    'bucket_upload' => 'NAME-BUCKET',
                    'options' => [
                        'version' => 'latest',
                        'region' => '',
                        'credentials' => [
                            'key'    => '',
                            'secret' => '',
                        ],
                    ],
                ],*/
                /*  'storage' => [
                 'name' => 'gs',
                 'bucket' => 'NAME-BUCKET',
                 'bucket_upload' => 'NAME-BUCKET',
                 'credentials_file' => 'tagncar-c26f3232cbb7.json',
                 'options' => [
                 'projectId' => *****
                 ]
                 ]*/
                
            ],
        ];
    }

    /**
     * Returns the container dependencies
     *
     * @return array
     */
    public function getDependencies() : array
    {
        return [
            'aliases' => [
                'dms.service' => \Dms\Service\DmsService::class,
                'dms.manager' => \Dms\Document\Manager::class,
                'Base64Coding' => 'BaseCoding',
                'UrlCoding' => \Dms\Coding\Url\Url::class,
                'Resize' => \Dms\Resize\Resize::class,
                'Storage' => \Dms\Storage\Storage::class,
            ],
            'invokables' => [
                'BaseCoding' => \Dms\Coding\Base\Base::class,
                'GzipCoding' => \Dms\Coding\Gzip\Gzip::class,
                'ZlibCoding' => \Dms\Coding\Zlib\Zlib::class,
            ],
            'factories' => [
                \Dms\Coding\Url\Url::class => function ($container) {
                    $config = $container->get('Config');
                    $url = new Url();
                    $url->setAdapter($config[$config['dms-conf']['adapter']]);
                    
                    return $url;
                },
                \Dms\Resize\Resize::class => function ($container) {
                    $config = $container->get('config')['dms-conf'];
                    
                    return new Resize([
                        'allow' => $config['size_allowed'],
                        'active' => $config['check_size_allowed'],
                    ]);
                },
                \Dms\Storage\Storage::class => function ($container) {
                    $config = $container->get('config')['dms-conf'];
                    
                    return new Storage([
                        'path' => $config['default_path'],
                        'storage' => $config['storage'],
                    ]);
                },
                \Dms\Document\Manager::class => function ($container) {
                    $config = $container->get('config')['dms-conf'];
                    
                    return new Manager($config, $container);
                },
                \Dms\Service\DmsService::class => function ($container) {
                    $config = $container->get('config')['dms-conf'];
                    
                    return new DmsService($container->get(\Dms\Document\Manager::class), $config);
                },
                Action\FileviewAction::class => function ($container) {
                    
                    return new Action\FileviewAction(
                        $container->get('config')['dms-conf']['headers'],
                        $container->get(\Dms\Service\DmsService::class));
                },
                Action\InitsessionAction::class => function ($container) {
                    
                    return new Action\InitsessionAction(
                        $container->get('config')['dms-conf']['headers'],
                        $container->get(\Dms\Service\DmsService::class));
                },
                Action\FileInfoAction::class => function ($container) {
                    
                    return new Action\FileInfoAction(
                        $container->get('config')['dms-conf']['headers'],
                        $container->get(\Dms\Service\DmsService::class));
                },
                Action\FileprogressAction::class => function ($container) {
                    
                    return new Action\FileprogressAction(
                        $container->get('config')['dms-conf']['headers'],
                        $container->get(\Dms\Service\DmsService::class));
                },
                Action\FilesaveAction::class => function ($container) {
                    
                    return new Action\FilesaveAction(
                        $container->get('config')['dms-conf']['headers'], 
                        $container->get(\Dms\Service\DmsService::class));
                },
                Action\FileDownloadAction::class => function ($container) {
                    return new Action\FileDownloadAction(
                        $container->get('config')['dms-conf']['headers'],
                        $container->get(\Dms\Service\DmsService::class));
                },
                Action\FileCopyAction::class => function ($container) {
                    
                },
            ],
            'abstract_factories' => [
                \Dms\ServiceFactory\CodingFactory::class,
            ],
        ];
    }
    
    /**
     * Get routes
     * 
     * @return array
     */
    public function getRoutes() : array
    {
        return [
        	[
                'name'            => 'fileview',
                'path'            => '/data/{file}',
        	    'middleware'      => Action\FileViewAction::class,
                'allowed_methods' => ['GET', 'OPTIONS'],
        	],[
        	    'name'            => 'fileformat',
        	    'path'            => '/format/{file}',
        	    'middleware'      => Action\FileInfoAction::class,
        	    'allowed_methods' => ['POST', 'GET', 'OPTIONS'],
        	],[
        	    'name'            => 'filetype',
        	    'path'            => '/type/{file}',
        	    'middleware'      => Action\FileInfoAction::class,
        	    'allowed_methods' => ['POST', 'GET', 'OPTIONS'],
        	],[
        	    'name'            => 'filesave',
        	    'path'            => '/save[/]',
        	    'middleware'      => Action\FileSaveAction::class,
        	    'allowed_methods' => ['POST', 'GET', 'OPTIONS'],
        	],[
        	    'name'            => 'filenamne',
        	    'path'            => '/name/{file}',
        	    'middleware'      => Action\FileInfoAction::class,
        	    'allowed_methods' => ['POST', 'GET', 'OPTIONS'],
        	],[
        	    'name'            => 'filedownload',
        	    'path'            => '/download/{file}',
        	    'middleware'      => Action\FileDownloadAction::class,
        	    'allowed_methods' => ['POST', 'GET', 'OPTIONS'],
        	],[
        	    'name'            => 'filecopy',
        	    'path'            => '/copy[/]',
        	    'middleware'      => Action\FileCopyAction::class,
        	    'allowed_methods' => ['POST', 'OPTIONS'],
        	],[
                'name'            => 'initsession',
                'path'            => '/initsession[/]', 
                'middleware'      =>  Action\InitSessionAction::class,
                'allowed_methods' => ['POST', 'GET', 'OPTIONS'],
            ],[
                'name'            => 'fileprogress',
                'path'            => '/progress[/]', 
                'middleware'      => Action\FileProgressAction::class,
                'allowed_methods' => ['POST', 'GET', 'OPTIONS'],
            ],[
                'name'            => 'filedescription',
                'path'            => '/description/{file}',
                'middleware'      => Action\FileInfoAction::class,
                'allowed_methods' => ['POST', 'GET', 'OPTIONS'],
            ],
        ];
    }
}
