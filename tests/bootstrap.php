<?php

namespace DmsTest;

error_reporting(E_ALL | E_STRICT);
chdir(__DIR__);

/**
 * Test bootstrap, for setting up autoloading
*/
class bootstrap
{

    public static function init()
    {
       // session_start();
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
        ini_set('date.timezone',"Europe/Paris");
        system('cp -r ./_upload ./upload');
        static::initAutoloader();
    }

    protected static function initAutoloader()
    {
        $vendorPath = static::findParentPath('vendor');
        
        $loader = include $vendorPath.'/autoload.php';
    }

    protected static function findParentPath($path)
    {
        $dir = __DIR__;
        $previousDir = '.';
        while (!is_dir($dir . '/' . $path)) {
            $dir = dirname($dir);
            if ($previousDir === $dir) return false;
            $previousDir = $dir;
        }

        return $dir . '/' . $path;
    }

}

Bootstrap::init();
