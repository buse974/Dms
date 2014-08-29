<?php

namespace Dms\Storage;

use Zend\Stdlib\AbstractOptions;

class StorageOption extends AbstractOptions
{
    private $path;

    public function setPath($path)
    {
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
