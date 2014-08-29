<?php

namespace Dms\Document;

use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Stdlib\Exception;

class Manager implements ServiceLocatorAwareInterface
{
    /**
     *
     * @var \Dms\Document\Document
     */
    protected $document;
    protected $serviceLocator;
    protected $storage;

    /**
     * Get a document
     * @param  integer|Document $documentOrId
     * @return Document
     */
    public function getDocumentById($id)
    {
        if (!is_string($id)) {
            throw new \Exception('Param is not numeric' . $id);
        }
        $info = $this->getStorage()->read($id .'.inf');

        if (!$info) {
            throw new \Exception('Not document with ' . $id . ' id');
        }

        $this->document = unserialize($info);
        $this->initData();

        return $this->document;
    }

    /**
     * Get a document
     * @param  integer|Document $documentOrId
     * @return bool
     */
    public function initData($document = null)
    {
        if ($document) {
            $this->setDocument($document);
        }

        $datas = $this->getStorage()->read($this->document->getId() . '.dat');
        if ($datas) {
            $this->document->setDatas($datas);
        }

        return true;
    }

    /**
     * Get a document
     * @param  integer|Document $documentOrId
     * @return Document
     */
    public function getInfoDocument($document = null)
    {
        if ($document) {
            $this->setDocument($document);
        }

        $info = $this->getStorage()->read($this->document->getId() .'.inf');

        return (!$info)?null:unserialize($info);
    }

    public function getDocument()
    {
        return $this->document;
    }

    public function setDocument($documentOrArray)
    {
        $document = $documentOrArray;

        switch (true) {
            case is_array($document) :
                $document = new Document();
                $document->setId((isset($documentOrArray['id'])) ? $documentOrArray['id'] : null)
                         ->setDatas((isset($documentOrArray['data'])) ? $documentOrArray['data'] : null)
                         ->setEncoding((isset($documentOrArray['coding'])) ? $documentOrArray['coding'] : null)
                         ->setType((isset($documentOrArray['type'])) ? $documentOrArray['type']: null)
                         ->setSupport((isset($documentOrArray['support'])) ? $documentOrArray['support'] : null)
                         ->setName((isset($documentOrArray['name'])) ? $documentOrArray['name'] : null)
                         ->setSize((isset($documentOrArray['size'])) ? $documentOrArray['size'] : null)
                         ->setId((isset($documentOrArray['hash'])) ? $documentOrArray['hash']: null);
                break;
            case is_string($document) :
                $document = new Document();
                $document->setId($documentOrArray);
                break;
        }
        if (!$document instanceof Document) {
            throw new Exception('Document must be a string, array or document object');
        }

        return $this->document = $document;
    }

    /**
     * Record a document to the Dms
     *
     * @param  string|array|document $document
     * @return Document
     */
    public function recordDocument($document = null)
    {
        if ($document) {
            $this->setDocument($document);
        }

        $this->getStorage()->write($this->document->getDatas(), $this->document->getId() .'.dat',$this->document->getSupport());
        $this->getStorage()->write(serialize($this->document), $this->document->getId() .'.inf');

        return $this->document;
    }

    public function decode($document=null)
    {
        $type = 'binary';
        if ($document) {
            $this->setDocument($document);
        }

        if ($this->document->getEncoding()!=$type && $this->document->getSupport()=='data') {
            $encdec = $this->getEncDec($this->document->getEncoding());
            $this->document->setDatas($encdec->decode($this->document->getDatas()));
            $this->document->setEncoding($type);
        }

        return $this->document;
    }

    /**
     * @TODO if support != data and encoding != binary
     * @param  string         $str_size
     * @param  array|Document $document
     * @return boolean
     */
    public function resizeDocument($str_size, $document=null)
    {
        if ($document) {
            $this->setDocument($document);
        }

        $resize = $this->getServiceResize();

        try {
            $resize->setData($this->document->getDatas());
            $img = $resize->getResizeData($str_size);
            $this->document->setEncoding('binary');
            $this->document->setDatas($img);
            $this->document->setSize($str_size);
            $this->document->setType($resize->getType());
            $this->recordDocument();
        } catch (\Exception $e) {
            return false;
        }

        return $this->document;
    }

    /**
     * Get storage
     * @return \Dms\Storage\StorageInterface
     */
    public function getStorage()
    {
        if (!$this->storage) {
            $this->storage = $this->getServiceLocator()->get('Storage');
        }

        return $this->storage;
    }

    /**
     * Set Storage
     * @param  \Dms\Storage\StorageInterface $storage
     * @return \Dms\Document\Manager
     */
    public function setStorage(\Dms\Storage\StorageInterface $storage)
    {
        $this->storage = $storage;

        return $this;
    }

    /**
     *
     * @return \Dms\Coding\CodingInterface
     */
    protected function getEncDec($enc)
    {
        return $this->getServiceLocator()->get($enc . 'Coding');
    }

    /**
     * @return \Dms\Resize\Resize
     */
    protected function getServiceResize()
    {
        return $this->getServiceLocator()->get('Resize');
    }

    /**
     * Get service locator
     *
     * @return ServiceLocatorInterface
     */
    public function getServiceLocator()
    {
        return $this->serviceLocator;
    }

    /**
     * Set service locator
     *
     * @param ServiceLocatorInterface $serviceLocator
     */
    public function setServiceLocator(ServiceLocatorInterface $serviceLocator)
    {
        $this->serviceLocator = $serviceLocator;

        return $this;
    }

    public function clear()
    {
        $this->document = null;
        $this->storage = null;
    }
}
