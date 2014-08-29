<?php

namespace Dms\Coding\Url;

use Zend\Stdlib\AbstractOptions;

class UrlOption extends AbstractOptions
{
    private $adapter;

    public function setAdapter($adapter)
    {
        $this->adapter = $adapter;

        return $this;
    }

    public function getAdapter()
    {
        if (!$this->adapter) {
            $this->adapter = array();
        }

        return $this->adapter;
    }
}
