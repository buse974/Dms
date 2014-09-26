<?php

namespace Dms\Coding\Url;

use Dms\Coding\CodingInterface;
use Zend\Http\Client;

class Url implements CodingInterface
{
    private $data;
    private $name = self::CODING_URL_STR;
    private $options;

    /**
     * @param array $options
     */
    public function __construct(array $options = array())
    {
        $this->options = new UrlOption($options);
    }

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

        return null;
    }

    public function decode($data = null)
    {
        if ($data!=null) {
            $this->setData($data);
        }

        return $this->getCurlData();
    }

    public function getCoding()
    {
        return $this->name;
    }

    private function getCurlData()
    {
        $cli = new Client();
        $cli->setAdapter($this->options->getAdapter());
        $cli->setUri($this->data);
        $ret = $cli->send();

        if ($ret->isClientError()) {
            if ($ret->getStatusCode()==403) {
                throw new ForbiddenDocumentException($this->data);
            }
            throw new ErrorDocumentException($ret->getReasonPhrase(),$ret->getStatusCode());
        }
        $data = $ret->getBody();
        /*if ($this->type===null) {
            $mime = $ret->getHeaders()->get('Content-Type');
            if ($mime) {
                $mime = $mime->getFieldValue();
            }
            if (empty($mime)) {
                $tt = strrchr($this->data, '.' );
                $mtype = new MimeType();
                $mime = $mtype->getMimeTypeByExtension($tt);
            }
            if (empty($mime)) {
                $fi = new finfo(FILEINFO_MIME);
                $mime = $fi->buffer($this->data);
            }
            $this->type = $mime;
        }*/

        return $data;
    }
}
