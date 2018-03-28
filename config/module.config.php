<?php

use Dms\Controller\Plugin\DmsFactory;

return [
    'view_manager' => [
        'strategies' => [
            'ViewJsonStrategy',
        ],
    ],
    'controller_plugins' => [
        'factories' => [
            'dms' => DmsFactory::class,
        ],
    ],
    'router' => [
        'routes' => [
            'fileview' => [
                'type' => 'Segment',
                'options' => [
                    'route' => '/data/:file',
                    'defaults' => [
                        'controller' => 'ged_document',
                        'action' => 'get',
                    ],
                ],
            ],
            'filedownload' => [
                'type' => 'Segment',
                'options' => [
                    'route' => '/download/:file',
                    'defaults' => [
                        'controller' => 'ged_document',
                        'action' => 'getDownload',
                    ],
                ],
            ],
            'filetype' => [
                'type' => 'Segment',
                'options' => [
                    'route' => '/type/:file',
                    'defaults' => [
                        'controller' => 'ged_document',
                        'action' => 'getType',
                    ],
                ],
            ],
            'filenamne' => [
                'type' => 'Segment',
                'options' => [
                    'route' => '/name/:file',
                    'defaults' => [
                        'controller' => 'ged_document',
                        'action' => 'getName',
                    ],
                ],
            ],
            'fileformat' => [
                'type' => 'Segment',
                'options' => [
                    'route' => '/format/:file',
                    'defaults' => [
                        'controller' => 'ged_document',
                        'action' => 'getFormat',
                    ],
                ],
            ],
            'filedescription' => [
                'type' => 'Segment',
                'options' => [
                    'route' => '/description/:file',
                    'defaults' => [
                        'controller' => 'ged_document',
                        'action' => 'getDescription',
                    ],
                ],
            ],
            'filesave' => [
                'type' => 'Segment',
                'options' => [
                    'route' => '/save[/]',
                    'defaults' => [
                        'controller' => 'ged_document',
                        'action' => 'save',
                    ],
                ],
            ],
            'filecopy' => [
                'type' => 'Segment',
                'options' => [
                    'route' => '/copy[/]',
                    'defaults' => [
                        'controller' => 'ged_document',
                        'action' => 'copy',
                    ],
                ],
            ],
            'fileprogress' => [
                'type' => 'Segment',
                'options' => [
                    'route' => '/progress[/]',
                    'defaults' => [
                        'controller' => 'ged_document',
                        'action' => 'progress',
                    ],
                ],
            ],
            'initsession' => [
                'type' => 'Segment',
                'options' => [
                    'route' => '/initsession[/]',
                    'defaults' => [
                        'controller' => 'ged_document',
                        'action' => 'initSession',
                    ],
                ],
            ],
        ],
    ],
    'controllers' => [
        'invokables' => [
            'ged_document' => 'Dms\Controller\DocumentController',
        ],
    ],
];
