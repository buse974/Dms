<?php
/**
 * @copyright Copyright (c) 2014 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 */

return array(
    'dms-conf' => array(
    	'check_size_allowed' => false,
        'size_allowed' => array(
            array('width' => 300, 'height' => 200),
            array('width' => 300, 'height' => 300),
            array('width' => 200, 'height' => 200),
            array('width' => 150, 'height' => 100),
            array('width' => 80, 'height' => 80),
            array('width' => 300),
        ),
        'default_path' => __DIR__ . '/../../_upload/',
        'adapter' => 'http-adapter',
        'headers'=> array(
            'Access-Control-Allow-Origin'=>'http://local.wow.in',
            'Access-Control-Allow-Credentials'=>'true'
        ),
        'convert' => array(
            'tmp' =>  __DIR__ . '/../../_tmp/',
        ),
    ),
);
