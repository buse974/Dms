<?php

namespace Dms\Resize;

use Zend\Stdlib\AbstractOptions;

class ResizeOption extends AbstractOptions
{
    private $allow;
    private $active;

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

    public function setActive($active)
    {
        $this->active = $active;

        return $this;
    }

    public function getActive()
    {
        return $this->active;
    }
}
