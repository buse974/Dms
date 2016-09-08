<?php

namespace Dms;

use Zend\ModuleManager\Feature\ConfigProviderInterface;
use Dms\Document\Manager;
use Dms\Coding\Url\Url;
use Dms\Resize\Resize;
use Dms\Storage\Storage;
use Dms\Service\DmsService;

class Module implements ConfigProviderInterface
{

    public function getConfig()
    {
        return include __DIR__.'/config/module.config.php';
    }

    public function getServiceConfig()
    {
        return [
            'aliases' =>[
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
            'abstract_factories' => [
                \Dms\ServiceFactory\CodingFactory::class,
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
                    return new Storage(['path' => $container->get('config')['dms-conf']['default_path']]);
                },
                \Dms\Document\Manager::class => function ($container) {
                    return new Manager($container->get('Config')['dms-conf'], $container);
                },
                \Dms\Service\DmsService::class => function ($container) {
                    return new DmsService($container->get(\Dms\Document\Manager::class));
                },
            ],
        ];
    }
}
