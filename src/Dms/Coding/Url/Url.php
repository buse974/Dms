<?php

namespace Dms\Coding\Url;

use Dms\Coding\CodingInterface;
use Zend\Http\Client;
use Zend\Http\Client\Adapter\Socket;
use Dms\Coding\Url\Exception\ErrorDocumentException;
use Dms\Coding\Url\Exception\ForbiddenDocumentException;

class Url implements CodingInterface
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
    private $name = self::CODING_URL_STR;

    /**
     *
     * @var \Zend\Http\Client\Adapter\AdapterInterface
     */
    private $adapter;

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
     * set uri to get
     *
     * @param  string              $data
     * @return \Dms\Coding\Url\Url
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

        return false;
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

        $client = new Client();
        $client->setAdapter($this->getAdapter());
        $client->setUri($this->data);
        $response = $client->send();

        if ($response->isClientError()) {
            if ($response->getStatusCode() == 403) {
                throw new ForbiddenDocumentException($this->data);
            }
            throw new ErrorDocumentException($response->getReasonPhrase(), $response->getStatusCode());
        }
        $data = $response->getBody();

        return $data;
    }

    /**
     * (non-PHPdoc)
     * @see \Dms\Coding\CodingInterface::getCoding()
     */
    public function getCoding()
    {
        return $this->name;
    }

    /**
     * Set adapter
     *
     * @param  \Zend\Http\Client\Adapter\AdapterInterface $adapter
     * @return \Dms\Coding\Url\Url
     */
    public function setAdapter($adapter)
    {
        $this->adapter = $adapter;

        return $this;
    }

    /**
     * return adapter
     *
     * @return \Zend\Http\Client\Adapter\AdapterInterface
     */
    public function getAdapter()
    {
        if (null === $this->adapter) {
            $this->adapter = new Socket();
        }

        return $this->adapter;
    }
}
