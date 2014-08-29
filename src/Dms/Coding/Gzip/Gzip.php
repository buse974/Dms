<?php

namespace Dms\Coding\Gzip;

use Dms\Coding\CodingInterface;

class Gzip implements CodingInterface
{
    private $name = 'gzip';
    private $data;

    public function getData()
    {
        return $this->data;
    }

    public function setData($data)
    {
        $this->data = $data;

        return $this;
    }

    public function encode($data = null)
    {
        if ($data!=null) {
            $this->setData($data);
        }

        return gzencode($this->data);
    }

    public function decode($data = null)
    {
        if ($data!=null) {
            $this->setData($data);
        }

        return gzdecode($this->data);
    }

    public function getCoding()
    {
        return $this->name;
    }
}
