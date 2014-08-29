<?php

namespace Dms\Storage;

use Zend\ServiceManager\ServiceManagerAwareInterface;

interface StorageInterface extends ServiceManagerAwareInterface
{
    const SUP_DATA = 'data';
    const SUP_FILE = 'file';
    const SUP_FILE_MULTI_PART = 'file_multi_part';

    public function write($data, $token, $enc);
    public function read($token);
}
