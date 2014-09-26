<?php

namespace Dms\Coding\Zlib;

use Dms\Coding\CodingInterface;

class Zlib implements CodingInterface
{
    private $name = self::CODING_ZLIB_STR;
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

        return gzcompress($this->data);
    }

    public function decode($data = null)
    {
        if ($data!=null) {
            $this->setData($data);
        }

        return gzuncompress($this->data);
    }

    public function getCoding()
    {
        return $this->name;
    }
}
