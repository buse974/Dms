<?php

use Dms\Controller\Plugin\DmsFactory;

return array(
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
        'router' => array(
            'routes' => array(
                'fileview' => array(
                    'type' => 'Segment',
                    'options' => array(
                        'route' => '/data/:file',
                        'defaults' => array(
                            'controller' => 'ged_document',
                            'action' => 'get',
                        ),
                    ),
                ),
                'filedownload' => array(
                    'type' => 'Segment',
                    'options' => array(
                        'route' => '/download/:file',
                        'defaults' => array(
                                'controller' => 'ged_document',
                                'action' => 'getDownload',
                        ),
                    ),
                ),
                'filetype' => array(
                            'type' => 'Segment',
                            'options' => array(
                                    'route' => '/type/:file',
                                    'defaults' => array(
                                            'controller' => 'ged_document',
                                            'action' => 'getType',
                                ),
                        ),
                ),
                'filenamne' => array(
                            'type' => 'Segment',
                            'options' => array(
                                    'route' => '/name/:file',
                                    'defaults' => array(
                                            'controller' => 'ged_document',
                                            'action' => 'getName',
                                ),
                        ),
                ),
                'fileformat' => array(
                    'type' => 'Segment',
                    'options' => array(
                        'route' => '/format/:file',
                        'defaults' => array(
                            'controller' => 'ged_document',
                            'action' => 'getFormat',
                        ),
                    ),
                ),
                'filedescription' => array(
                            'type' => 'Segment',
                            'options' => array(
                                    'route' => '/description/:file',
                                    'defaults' => array(
                                            'controller' => 'ged_document',
                                            'action' => 'getDescription',
                                ),
                        ),
                ),
                'filesave' => array(
                            'type' => 'Segment',
                            'options' => array(
                                    'route' => '/save[/]',
                                    'defaults' => array(
                                            'controller' => 'ged_document',
                                            'action' => 'save',
                                    ),
                            ),
                ),
                'fileprogress' => array(
                            'type' => 'Segment',
                            'options' => array(
                                    'route' => '/progress[/]',
                                    'defaults' => array(
                                            'controller' => 'ged_document',
                                            'action' => 'progress',
                                    ),
                            ),
                ),
                'initsession' => array(
                            'type' => 'Segment',
                            'options' => array(
                                    'route' => '/initsession[/]',
                                    'defaults' => array(
                                            'controller' => 'ged_document',
                                            'action' => 'initSession',
                                    ),
                            ),
                ),
            ),
        ),
        'controllers' => array(
            'invokables' => array(
                'ged_document' => 'Dms\Controller\DocumentController',
            ),
        ),
);
