<?php

namespace Dms\Coding\Gzip;

use Dms\Coding\CodingInterface;

class Gzip implements CodingInterface
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
    private $name = self::CODING_GZIP_STR;

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
     * @return \Dms\Coding\Gzip\Gzip
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

        return gzencode($this->data);
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

        return gzdecode($this->data);
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
