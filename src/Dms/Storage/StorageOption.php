<?php

namespace Dms\Storage;

use Zend\Stdlib\AbstractOptions;

class StorageOption extends AbstractOptions
{
    private $path;

    public function setPath($path)
    {
        if(substr($path, -1) !== DIRECTORY_SEPARATOR) {
            $path .= DIRECTORY_SEPARATOR;
        }
            
        $this->path = $path;

        return $this;
    }

    public function getPath()
    {
        if (!$this->path) {
            $this->path = array();
        }

        return $this->path;
    }
}
