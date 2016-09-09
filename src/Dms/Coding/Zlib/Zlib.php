<?php
/**
 * github.com/buse974/Dms (https://github.com/buse974/Dms).
 *
 * Encode/decode Zlib
 */
namespace Dms\Coding\Zlib;

use Dms\Coding\CodingInterface;

/**
 * Class Zlib.
 */
class Zlib implements CodingInterface
{
    /**
     * data for encoded or decoded.
     *
     * @var string
     */
    private $data;

    /**
     * Name Coding.
     *
     * @var string
     */
    private $name = self::CODING_ZLIB_STR;

    /**
     * return string to encoded or decoded.
     *
     * @return string
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * set string to encoded or decoded.
     *
     * @param string $data
     *
     * @return \Dms\Coding\Zlib\Zlib
     */
    public function setData($data)
    {
        $this->data = $data;

        return $this;
    }

    /**
     * (non-PHPdoc).
     *
     * @param string $data
     *
     * @see \Dms\Coding\CodingInterface::encode()
     */
    public function encode($data = null)
    {
        if ($data != null) {
            $this->setData($data);
        }

        return gzcompress($this->data);
    }

    /**
     * (non-PHPdoc).
     *
     * @param string $data
     *
     * @see \Dms\Coding\CodingInterface::decode()
     */
    public function decode($data = null)
    {
        if ($data != null) {
            $this->setData($data);
        }

        return gzuncompress($this->data);
    }

    /**
     * (non-PHPdoc).
     *
     * @see \Dms\Coding\CodingInterface::getCoding()
     */
    public function getCoding()
    {
        return $this->name;
    }
}
