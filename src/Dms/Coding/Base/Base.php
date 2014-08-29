<?php

namespace Dms\Coding\Base;

use Dms\Coding\CodingInterface;

class Base implements CodingInterface
{
    private $data;
    private $name = 'base';

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

        return base64_encode($this->data);
    }

    public function decode($data = null)
    {
        if ($data!=null) {
            $this->setData($data);
        }

        $datPos = strpos ($this->data , 'base64,');
        if ($datPos!==false) {
             $data = substr($this->data, $datPos+7);
        }

        return base64_decode($this->data);
    }

    public function getCoding()
    {
        return $this->name;
    }
}
