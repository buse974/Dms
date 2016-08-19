<?php

namespace Dms;

use Zend\ModuleManager\Feature\ConfigProviderInterface;

class Module implements ConfigProviderInterface
{
    public function getAutoloaderConfig()
    {
        return array(
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                        __NAMESPACE__ => __DIR__.'/src/'.__NAMESPACE__,
                    ),
            ),
        );
    }

    public function getConfig()
    {
        return include __DIR__.'/config/module.config.php';
    }

    public function getServiceConfig()
    {
        return array(
            'aliases' => array(
                'dms.service' => \Dms\Service\DmsService::class,
                'dms.manager' => \Dms\Document\Manager::class,
                'Base64Coding' => 'BaseCoding',
                'UrlCoding' => '\Dms\Coding\Url\Url',
                'Resize' => '\Dms\Resize\Resize',
                'Storage' => '\Dms\Storage\Storage',
            ),
            'invokables' => array(
                'BaseCoding' => \Dms\Coding\Base\Base::class,
                'GzipCoding' => \Dms\Coding\Gzip\Gzip::class,
                'ZlibCoding' => \Dms\Coding\Zlib\Zlib::class,
                \Dms\Document\Manager::class => \Dms\Document\Manager::class,
                \Dms\Service\DmsService::class => \Dms\Service\DmsService::class,
            ),
            'abstract_factories' => array(
                'Dms\ServiceFactory\CodingFactory',
            ),
            'factories' => array(
                '\Dms\Coding\Url\Url' => function ($sm) {
                    $config = $sm->get('Config');
                    $url = new \Dms\Coding\Url\Url();
                    $url->setAdapter($config[$config['dms-conf']['adapter']]);

                    return $url;
                },
                '\Dms\Resize\Resize' => function ($sm) {
                    return new \Dms\Resize\Resize([
                            'allow' => $sm->get('config')['dms-conf']['size_allowed'],
                            'active' => $sm->get('config')['dms-conf']['check_size_allowed'],
                    ]);
                },
                '\Dms\Storage\Storage' => function ($sm) {
                    $dmsconf = $sm->get('config')['dms-conf'];
                    return new \Dms\Storage\Storage([
                        'path' => $dmsconf['default_path'],
                        'storage' => $dmsconf['storage']
                    ]);
                },
            ),
        );
    }
}
