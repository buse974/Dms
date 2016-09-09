<?php
return array(
    'modules' => array(
        'Dms',
        'Zend\Router',
    ),
    'module_listener_options' => array(
        'module_paths' => array(
            'Dms' => __DIR__ . '/../../',
        ),
        // An array of paths from which to glob configuration files after
        // modules are loaded. These effectively override configuration
        // provided by modules themselves. Paths may use GLOB_BRACE notation.
        'config_glob_paths' => array(
            __DIR__ . '/autoload/{,*.}{global,local}.php',
        ),
    ),
);
