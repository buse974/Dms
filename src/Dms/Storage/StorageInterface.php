<?php

namespace Dms\Storage;

use Zend\ServiceManager\ServiceManagerAwareInterface;

interface StorageInterface extends ServiceManagerAwareInterface
{
    public function write($data, $token, $enc);
    public function read($token);
}
