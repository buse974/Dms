<?php

namespace Dms\Coding\Deflate;

use Dms\Coding\CodingInterface;

class Deflate implements CodingInterface
{
    private $data;
    private $name = 'deflate';

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

        return gzdeflate($this->data);
    }

    public function decode($data = null)
    {
        if ($data!=null) {
            $this->setData($data);
        }

        return gzinflate($this->data);
    }

    public function getCoding()
    {
        return $this->name;
    }
}
