<?php

return array(
        'dms-conf' => array(
                'size_allowed' => array(
                        array('width' => 300, 'height' => 200),
                        array('width' => 300, 'height' => 300),
                        array('width' => 150, 'height' => 100),
                        array('width' => 80, 'height' => 80),
                        array('width' => 300),
                ),
                'default_path' => 'upload/',
                'adapter' => 'http-adapter',
                'headers'=> array(
                        'Access-Control-Allow-Origin'=>'http://local.wow.in',
                        'Access-Control-Allow-Credentials'=>'true'
                ),
        ),
        'view_manager' => array(
                'display_not_found_reason' => false,
                'display_exceptions'       => false,
                'strategies' => array(
                        'ViewJsonStrategy',
                ),
        ),
        'router' => array(
            'routes' => array(
                'fileview' => array(
                    'type' => 'Segment',
                    'options' => array(
                        'route'    => '/data/:file',
                        'defaults' => array(
                            'controller' => 'ged_document',
                            'action'     => 'get',
                        ),
                    ),
                ),
                'filetype' => array(
                            'type' => 'Segment',
                            'options' => array(
                                    'route'    => '/type/:file',
                                    'defaults' => array(
                                            'controller' => 'ged_document',
                                            'action'     => 'getType',
                                ),
                        ),
                ),
                'filenamne' => array(
                            'type' => 'Segment',
                            'options' => array(
                                    'route'    => '/name/:file',
                                    'defaults' => array(
                                            'controller' => 'ged_document',
                                            'action'     => 'getName',
                                ),
                        ),
                ),
                'filedescription' => array(
                            'type' => 'Segment',
                            'options' => array(
                                    'route'    => '/description/:file',
                                    'defaults' => array(
                                            'controller' => 'ged_document',
                                            'action'     => 'getDescription',
                                ),
                        ),
                ),
                'filesave' => array(
                            'type' => 'Segment',
                            'options' => array(
                                    'route'    => '/save[/]',
                                    'defaults' => array(
                                            'controller' => 'ged_document',
                                            'action'     => 'save',
                                    ),
                            ),
                ),
                'fileprogress' => array(
                            'type' => 'Segment',
                            'options' => array(
                                    'route'    => '/progress[/]',
                                    'defaults' => array(
                                            'controller' => 'ged_document',
                                            'action'     => 'progress',
                                    ),
                            ),
                ),
                'initsession' => array(
                            'type' => 'Segment',
                            'options' => array(
                                    'route'    => '/initsession[/]',
                                    'defaults' => array(
                                            'controller' => 'ged_document',
                                            'action'     => 'initSession',
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
