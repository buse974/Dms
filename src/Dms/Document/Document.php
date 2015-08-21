<?php

namespace Dms\Document;

use Zend\Validator\File\Sha1;
use Serializable;
use Dms\Storage\Storage;

/**
 * class Document is a File Model.
 */
class Document implements Serializable
{
    const TYPE_BINARY_STR = 'binary';
    const SUPPORT_DATA_STR = 'data';
    const SUPPORT_FILE_STR = 'file';
    const SUPPORT_FILE_MULTI_PART_STR = 'file_multi_part';

    /**
     * @var string
     */
    protected $id;

    /**
     * @var string
     */
    protected $hash;

    /**
     * @var number
     */
    protected $page;

    /**
     * @var string
     */
    protected $datas;

    /**
     * @var string
     */
    protected $support;

    /**
     * @var string
     */
    protected $encoding;

    /**
     * @var string
     */
    protected $type;

    /**
     * @var string
     */
    protected $format;

    /**
     * Document Name.
     *
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $description;

    /**
     * @var string
     */
    protected $size;

    /**
     * weight of document.
     *
     * @var number
     */
    protected $weight;

    /**
     * @var \Dms\Storage\StorageInterface
     */
    protected $storage;

    /**
     * @var bool
     */
    protected $is_read = false;

    /**
     * Constructor.
     *
     * @param String $encoding
     */
    public function __construct($encoding = null)
    {
        if (null !== $encoding) {
            $this->setEncoding($encoding);
        }
    }

    public function isRead()
    {
        $this->is_read = true;

        return $this;
    }

    /**
     * Get the document id.
     *
     * @return string
     */
    public function getId()
    {
        if (null === $this->id) {
            $this->id = $this->getHash().(($this->size) ? '-'.$this->size : '').(($this->page) ? '['.$this->page.']' : '').(($this->format) ? '.'.$this->format : '');
        }

        return $this->id;
    }

    /**
     * Setter Id Document.
     *
     * @param $id
     *
     * @return \Dms\Document\Document
     */
    public function setId($id)
    {
        $this->id = $id;
        preg_match('/(?P<hash>\w+)($|\-|\.)/', $id, $matches, PREG_OFFSET_CAPTURE);
        $this->hash = (isset($matches['hash']) && !empty($matches['hash'][0])) ? $matches['hash'][0] : null;

        return $this;
    }

    /**
     * Get body document.
     *
     * @return string
     */
    public function getDatas($print = null)
    {
        if ((null === $this->datas && null !== $this->getStorage()) || $print !== null) {
            $this->getStorage()->read($this, 'datas', $print);
        }

        return $this->datas;
    }

    /**
     * get format.
     *
     * @return string
     */
    public function getFormat()
    {
        if (null === $this->format && null !== $this->getStorage() && $this->is_read === false) {
            $this->read('format');
        }

        if (null === $this->format && null !== $this->type) {
            $this->format = MimeType::getExtensionByMimeType($this->type);
        }

        return $this->format;
    }

    /**
     * Set format.
     *
     * @param string $format
     *
     * @return \Dms\Document\Document
     */
    public function setFormat($format)
    {
        $this->format = $format;

        if ($type = MimeType::getMimeTypeByExtension($this->format)) {
            $this->type = $type;
        }

        return $this;
    }

    /**
     * Set body document.
     *
     * @param string $datas
     *
     * @return \Dms\Document\Document
     */
    public function setDatas($datas)
    {
        $this->datas = $datas;

        return $this;
    }

    /**
     * Get type of file.
     *
     * @return string
     */
    public function getType()
    {
        if (null === $this->type && null !== $this->getStorage() && $this->is_read === false) {
            $this->read('type');
        }

        return $this->type;
    }

    /**
     * Set type of File.
     *
     * @param string $type
     *
     * @return \Dms\Document\Document
     */
    public function setType($type)
    {
        $this->type = $type;

        if ($fmt = MimeType::getExtensionByMimeType($this->type)) {
            $this->format = $fmt;
        }

        return $this;
    }

    /**
     * Get page of file.
     *
     * @return number
     */
    public function getPage()
    {
        return $this->page;
    }

    /**
     * Set page of File.
     *
     * @param number $page
     *
     * @return \Dms\Document\Document
     */
    public function setPage($page)
    {
        $this->page = $page;

        return $this;
    }

    /**
     * Get Document Encoding.
     *
     * @return string
     */
    public function getEncoding()
    {
        if (null === $this->encoding && null !== $this->getStorage() && $this->is_read === false) {
            $this->read('encoding');
        }

        if (null === $this->encoding) {
            $this->encoding = self::TYPE_BINARY_STR;
        }

        return $this->encoding;
    }

    /**
     * Set document encoding.
     *
     * @param encoding
     *
     * @return \Dms\Document\Document
     */
    public function setEncoding($encoding)
    {
        $this->encoding = $encoding;

        return $this;
    }

    /**
     * Get Name.
     *
     * @return string
     */
    public function getName()
    {
        if (null === $this->name && null !== $this->getStorage() && $this->is_read === false) {
            $this->read('name');
        }

        if (null === $this->name) {
            preg_match('/(?P<hash>\w+)($|\-|\.)/', $this->id, $matches, PREG_OFFSET_CAPTURE);
            $this->name = (isset($matches['hash']) && !empty($matches['hash'][0])) ? $matches['hash'][0].'.'.$this->getFormat() : null;
        }

        return $this->name;
    }

    /**
     * Set Name.
     *
     * @param string $name
     *
     * @return \Dms\Document\Document
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get Description Document.
     *
     * @return string
     */
    public function getDescription()
    {
        if (null === $this->description && null !== $this->getStorage() && $this->is_read === false) {
            $this->read('description');
        }

        return $this->description;
    }

    /**
     * Set Description Document.
     *
     * @param string $description
     *
     * @return \Dms\Document\Document
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get size.
     *
     * @return string
     */
    public function getSize()
    {
        if (null === $this->size && null !== $this->getStorage() && $this->is_read === false) {
            $this->read('size');
        }

        return $this->size;
    }

    /**
     * Set size.
     *
     * @param string $size
     *
     * @return \Dms\Document\Document
     */
    public function setSize($size)
    {
        $this->size = $size;

        return $this;
    }

    /**
     * @param string $support
     */
    public function setSupport($support)
    {
        $this->support = $support;

        return $this;
    }

    /**
     * @param string $support
     */
    public function getSupport()
    {
        if (null === $this->support && null !== $this->getStorage() && $this->is_read === false) {
            $this->read('support');
        }

        if (null === $this->support) {
            $this->support = self::SUPPORT_DATA_STR;
        }

        return $this->support;
    }

    /**
     * Get Hash.
     *
     * @return string
     */
    public function getHash()
    {
        if (!$this->hash) {
            $this->hash = sha1($this->name.uniqid($_SERVER['REMOTE_ADDR'], true));
        }

        return $this->hash;
    }

    /**
     * Set Hash.
     *
     * @param string
     */
    public function setHash($hash)
    {
        $this->hash = $hash;

        return $this;
    }

    /**
     * Set weight of document.
     *
     * @param number $weight
     *
     * @return \Dms\Document\Document
     */
    public function setWeight($weight)
    {
        $this->weight = $weight;

        return $this;
    }

    /**
     * Get weight of document.
     *
     * @return number
     */
    public function getWeight()
    {
        if (null === $this->weight && null !== $this->getStorage() && $this->is_read === false) {
            $this->read('weight');
        }

        return  $this->weight;
    }

    /**
     * (non-PHPdoc).
     *
     * @see Serializable::serialize()
     */
    public function serialize()
    {
        return serialize($this->toArray());
    }

    public function toArray()
    {
        return array(
                'id' => $this->getId(),
                'size' => $this->getSize(),
                'name' => $this->getName(),
                'type' => $this->getType(),
                'hash' => $this->getHash(),
                'description' => $this->getDescription(),
                'encoding' => $this->getEncoding(),
                'support' => $this->getSupport(),
                'weight' => $this->getWeight(),
                'format' => $this->getFormat(),
        );
    }

    public function getPathDat()
    {
        return $this->getStorage()->getPath($this, '.dat');
    }

    public function getPathInf()
    {
        return $this->getStorage()->getPath($this, '.inf');
    }

    /**
     * (non-PHPdoc).
     *
     * @see Serializable::unserialize()
     */
    public function unserialize($serialized)
    {
        $datas = unserialize($serialized);
        $this->setId($datas['id']);
        $this->setSize((isset($datas['size']) ? $datas['size'] : null));
        $this->setName($datas['name']);
        $this->setType($datas['type']);
        $this->setDescription($datas['description']);
        $this->setEncoding($datas['encoding']);
        $this->setSupport($datas['support']);
        $this->setHash((isset($datas['hash'])) ? $datas['hash'] : null);
        $this->setWeight((isset($datas['weight'])) ? $datas['weight'] : null);
        $this->setFormat((isset($datas['format'])) ? $datas['format'] : null);
    }

    public function read($type)
    {
        if ($this->exist()) {
            $this->getStorage()->read($this, $type);
            $this->is_read = true;
        }

        return $this;
    }

    public function exist()
    {
        return $this->getStorage()->exist($this);
    }
    /**
     * Get storage.
     *
     * @return \Dms\Storage\StorageInterface
     */
    public function getStorage()
    {
        return $this->storage;
    }

    /**
     * Set Storage.
     *
     * @param \Dms\Storage\StorageInterface $storage
     *
     * @return \Dms\Document\Manager
     */
    public function setStorage(\Dms\Storage\StorageInterface $storage)
    {
        $this->storage = $storage;

        return $this;
    }

    public function write()
    {
        $this->getStorage()->write($this);
    }
}
