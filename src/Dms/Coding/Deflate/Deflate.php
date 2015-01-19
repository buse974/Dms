<?php

namespace Dms\Coding\Deflate;

use Dms\Coding\CodingInterface;

class Deflate implements CodingInterface
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
    private $name = self::CODING_DEFLATE_STR;

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
     * @param  string                      $data
     * @return \Dms\Coding\Deflate\Deflate
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

        return gzdeflate($this->data);
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

        return gzinflate($this->data);
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
