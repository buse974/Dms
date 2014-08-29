<?php

namespace Dms\Resize;

use Zend\Stdlib\AbstractOptions;

class ResizeOption extends AbstractOptions
{
    private $allow;

    public function setAllow($allow)
    {
        $this->allow = $allow;

        return $this;
    }

    public function getAllow()
    {
        if (!$this->allow) {
            $this->allow = array();
        }

        return $this->allow;
    }
}
