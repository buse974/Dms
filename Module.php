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
                            __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
                        ),
                ),
                  'Zend\Loader\ClassMapAutoloader' => array(
                       __DIR__ . '/autoload_classmap.php',
                ),
        );
    }

    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }

    public function getServiceConfig()
    {
        return array(
            'aliases' => array(
                 'dms.service'    => 'Dms\Service\DmsService',
                 'dms.manager'    => 'Dms',
                 'Base64Coding' => 'BaseCoding'
            ),
            'invokables' => array(
                'BaseCoding'                => '\Dms\Coding\Base\Base',
                'GzipCoding'                => '\Dms\Coding\Gzip\Gzip',
                'ZlibCoding'                => '\Dms\Coding\Zlib\Zlib',
                'Dms'                        => 'Dms\Document\Manager',
                'Dms\Service\DmsService'    => 'Dms\Service\DmsService',
            ),
            'abstract_factories' => array(
                'Dms\ServiceFactory\CodingFactory'
            ),
            'factories' => array(
                    'UrlCoding' => function ($sm) {
                        $config = $sm->get('Config');

                        return new \Dms\Coding\Url\Url(array('adapter' => $config[$config['dms-conf']['adapter']]));
                    },
                    'Resize' => function ($sm) {
                        return new \Dms\Resize\Resize(array('allow' => $sm->get('config')['dms-conf']['size_allowed']));
                    },
                    'Storage' => function ($sm) {
                         return new \Dms\Storage\Storage(array('path' => $sm->get('config')['dms-conf']['default_path']));
                    },
            ),
        );
    }
}
