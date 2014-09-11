<?php

namespace Dms\Document;

use Zend\Validator\File\Sha1;
use \Serializable;

/**
 * class Document is a File Model
 *
 */
class Document implements Serializable
{
	const TYPE_BINARY_STR = 'binary';
	const SUPPORT_DATA_STR = 'data';
	const SUPPORT_FILE_STR = 'file';
	const SUPPORT_FILE_MULTI_PART_STR = 'file_multi_part';
    /**
     *
     * @var string
     */
    protected $id;

    /**
     *
     * @var string
     */
    protected $hash;

    /**
     *
     * @var string
     */
    protected $datas;

    /**
     *
     * @var string
     */
    protected $support;

    /**
     *
     * @var string
     */
    protected $encoding;

    /**
     *
     * @var string
     */
    protected $type;

    /**
     * Document Name
     * 
     * @var string
     */
    protected $name;

    /**
     * 
     * @var string
     */
    protected $description;

    /**
     * 
     * @var string
     */
    protected $size;

    /**
     * weight of document
     * 
     * @var number
     */
    protected $weight;
    
    /**
     * Constructor
     * @param String $encoding
     */
    public function __construct($encoding = null)
    {
        if (null !== $encoding) {
            $this->setEncoding($encoding);
        }
    }

    /**
     * Get the document id
     * @return string
     */
    public function getId()
    {
        if (null === $this->id) {
            $this->id = (($this->size)? $this->getHash() . '-' . $this->size : $this->getHash());
        }

        return $this->id;
    }

    /**
     * Setter Id Document
     * @param $id
     * @return Document
     */
    public function setId($id)
    {
        $t = explode('-', $id);

        if (count($t) > 1) {
            $this->hash = $t[0];
            $this->size = $t[1];
        } else {
            $this->hash = $id;
        }

        $this->getId();

        return $this;
    }

    /**
     * Get body document
     * @return string
     */
    public function getDatas()
    {
        return $this->datas;
    }

    /**
     * Set body document
     * @param  string                 $datas
     * @return \Dms\Document\Document
     */
    public function setDatas($datas)
    {
        $this->datas = $datas;

        return $this;
    }

    /**
     * Get type of file
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set type of File
     *
     * @param  string                 $type
     * @return \Dms\Document\Document
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get Document Encoding
     *
     * @return string
     */
    public function getEncoding()
    {
        if (null === $this->encoding) {
            $this->encoding = 'binary';
        }

        return $this->encoding;
    }

    /**
     * Set document encoding
     *
     * @param encoding
     * @return \Dms\Document\Document
     */
    public function setEncoding($encoding)
    {
        $this->encoding = $encoding;

        return $this;
    }

    /**
     * Get Name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set Name
     *
     * @param  string                 $name
     * @return \Dms\Document\Document
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get Description Document
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set Description Document
     *
     * @param  string                 $description
     * @return \Dms\Document\Document
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get size
     *
     * @return string
     */
    public function getSize()
    {
        return $this->size;
    }

    /**
     * Set size
     *
     * @param  string                 $size
     * @return \Dms\Document\Document
     */
    public function setSize($size)
    {
        $this->size = $size;
        $id_tmp=$this->getId();
        $this->id=null;
        $this->setId($id_tmp . '-' . $size);

        return $this;
    }

    /**
     *
     * @param string $support
     */
    public function setSupport($support)
    {
        $this->support = $support;

        return $this;
    }

    /**
     *
     * @param string $support
     */
    public function getSupport()
    {
        if (null === $this->support) {
            $this->support='data';
        }

        return $this->support;
    }

    /**
     * Get Hash
     *
     * @return string
     */
    protected function getHash()
    {
        if (!$this->hash) {
            $this->hash = sha1($this->name . uniqid($_SERVER['REMOTE_ADDR'],true));
        }

        return $this->hash;
    }
    
    /**
     * Set weight of document
     * 
     * @param number $weight
     * @return \Dms\Document\Document
     */
    public function setWeight($weight)
    {
    	$this->weight = $weight;
    	
    	return $this;
    }
    
    /**
     * Get weight of document 
     * 
     * @return number
     */
    public function getWeight()
    {
    	return  $this->weight;
    }
    

    /**
     * (non-PHPdoc)
     * @see Serializable::serialize()
     */
    public function serialize()
    {
    	return serialize(array(
    			'id' => $this->getId(),
    			'size' => $this->getSize(),
    			'name' => $this->getName(),
    			'type' => $this->getType(),
    			'description' => $this->getDescription(),
    			'encoding' => $this->getEncoding(),
    			'support' => $this->getSupport(),
    			'weight' => $this->getWeight(),
    	));
    }
    
    /**
     * (non-PHPdoc)
     * @see Serializable::unserialize()
     */
    public function unserialize($serialized)
    {
    	$datas = unserialize($serialized);
    	$this->setId($datas['id']);
    	$this->setSize((isset($datas['size']) ? $datas['size'] : ''));
    	$this->setName($datas['name']);
    	$this->setType($datas['type']);
    	$this->setDescription($datas['description']);
    	$this->setEncoding($datas['encoding']);
    	$this->setSupport($datas['support']);
    	$this->setWeight((isset($datas['weight']))?$datas['weight']:null);
    }
}
