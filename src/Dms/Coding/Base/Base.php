<?php

namespace Dms\Coding\Base;

use Dms\Coding\CodingInterface;

class Base implements CodingInterface
{
    /**
     * data for encoded or decoded
     *
     * @var string
     */
    private $data;

    /**
     *
     * @var string
     */
    private $name = self::CODING_BASE_STR;

    /**
     * return string to encoded or decoded
     *
     * @return string
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * set string to encoded or decoded
     *
     * @param  string                $data
     * @return \Dms\Coding\Base\Base
     */
    public function setData($data)
    {
        $this->data = $data;

        return $this;
    }

    /**
     * (non-PHPdoc)
     * @see \Dms\Coding\CodingInterface::encode()
     */
    public function encode($data = null)
    {
        if ($data != null) {
            $this->setData($data);
        }

        return base64_encode($this->data);
    }

    /**
     * (non-PHPdoc)
     * @see \Dms\Coding\CodingInterface::decode()
     */
    public function decode($data = null)
    {
        if ($data != null) {
            $this->setData($data);
        }

        $datPos = strpos($this->data, 'base64,');
        if ($datPos !== false) {
            $this->data = substr($this->data, $datPos+7);
        }

        return base64_decode($this->data);
    }

    /**
     * (non-PHPdoc)
     * @see \Dms\Coding\CodingInterface::getCoding()
     */
    public function getCoding()
    {
        return $this->name;
    }
}
