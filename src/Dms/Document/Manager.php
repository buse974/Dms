<?php

namespace Dms\Document;

use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Dms\Storage\StorageInterface;

class Manager implements ServiceLocatorAwareInterface
{
    /**
     *
     * @var \Dms\Document\Document
     */
    protected $document;
    
    /**
     * 
     * @var string
     */
    protected $format;
    
    /**
     * 
     * @var string
     */
    protected $size;
    
    /**
     * 
     * @var \Zend\ServiceManager\ServiceLocatorInterface
     */
    protected $serviceLocator;
    
    /**
     * 
     * @var \Dms\Storage\StorageInterface
     */
    protected $storage;

    /**
     *  Load document
     * 
     * @param string|\Dms\Document\Document $document
     * @throws \Exception
     * @return \Dms\Document\Manager
     */
    public function loadDocument($document)
    {
    	if($document instanceof Document && is_string($document->getId())) {
    		$document = $document->getId();
    	}
    	if(!is_string($document)) {
    		throw new \Exception('Param is not id: ' . $id);
    	}

        $info = $this->getStorage()->read($document .'.inf');

        if (!$info) {
            throw new \Exception('Not document: ' . $id);
        }

        $this->document = unserialize($info);
        
        $datas = $this->getStorage()->read($document . '.dat');
        if ($datas) {
        	$this->document->setDatas($datas);
        }

        return $this;
    }

    /**
     * Load document info
     * 
     * @param string|\Dms\Document\Document $document
     * @throws \Exception
     * @return \Dms\Document\Manager
     */
    public function loadDocumentInfo($document)
    {
    	if($document instanceof Document && is_string($document->getId())) {
    		$document = $document->getId();
    	}
    	if(!is_string($document)) {
    		throw new \Exception('Param is not id: ' . $id);
    	}

        $info = $this->getStorage()->read($document .'.inf');
        
        if (!$info) {
        	throw new \Exception('Not document: ' . $id);
        }
        
        $this->document = unserialize($info);

        return $this;
    }

    /**
     * Get Document
     * 
     * @return \Dms\Document\Document
     */
    public function getDocument()
    {
        if(null === $this->document) {
        	$this->document = new Document();
        }
        	
        return $this->document;
    }

    /**
     * Initialise Document with a array
     * 
     * @param array $document
     * @return \Dms\Document\Manager
     */
    public function createDocument(array $document)
    {
    	$this->clear();
        $this->document = new Document();
        $this->document->setId((isset($document['id'])) ? $document['id'] : null)
                       ->setDatas((isset($document['data'])) ? $document['data'] : null)
                       ->setEncoding((isset($document['coding'])) ? $document['coding'] : null)
                       ->setType((isset($document['type'])) ? $document['type']: null)
                       ->setSupport((isset($document['support'])) ? $document['support'] : null)
                       ->setName((isset($document['name'])) ? $document['name'] : null)
                       ->setSize((isset($document['size'])) ? $document['size'] : null)
                       ->setWeight((isset($document['weight'])) ? $document['weight'] : null)
                       ->setId((isset($document['hash'])) ? $document['hash']: null);

        return $this;
    }

    /**
     * Record a document to the Dms
     *
     * @return \Dms\Document\Manager
     */
    public function writeFile()
    {
        if (null === $this->document) {
            throw new \Exception('Document does not exist');
        }
		if(null !== $this->size) {
			$this->resize();
		}
		if(null !== $this->format) {
			$this->convert();
		}
		
        $this->getStorage()->write($this->document->getDatas(), $this->document->getId() .'.dat',$this->document->getSupport());
        $this->document->setSupport(Document::SUPPORT_FILE_STR);
        $this->getStorage()->write(serialize($this->document), $this->document->getId() .'.inf');

        return $this;
    }

    /**
     * Decode Document to binary
     * 
     * @throws \Exception
     * @return \Dms\Document\Manager
     */
    public function decode()
    {
    	if (null === $this->document) {
    		throw new \Exception('Document does not exist');
    	}
    	
        if ($this->document->getEncoding()!=Document::TYPE_BINARY_STR && $this->document->getSupport()==Document::SUPPORT_DATA_STR) {
            $this->document->setDatas($this->getEncDec($this->document->getEncoding())->decode($this->document->getDatas()));
            $this->document->setEncoding(Document::TYPE_BINARY_STR);
        }

        return $this;
    }

    /**
     * Resize document
     * 
     * @param number $size
     * @return \Dms\Document\Manager
     */
    private function resize()
    {
        try {
        	$resize = $this->getServiceResize();
            $resize->setData($this->document->getDatas());
            $this->document->setEncoding(Document::TYPE_BINARY_STR);
            $this->document->setDatas($resize->getResizeData($this->size));
            $this->document->setSize($this->size);
            $this->document->setType($resize->getType());
        } catch (\Exception $e) {
            throw $e;
        }

        return $this;
    }

    /**
     * 
     */
    private function convert()
    {
    	
    }

    public function getSize()
    {
    	return $this->size;
    }
    
    public function setSize($size)
    {
    	$this->size = $size;
    	
    	return $this;
    }
    
    public function getFormat()
    {
    	return $this->format;
    }
    
    public function setFormat($format)
    {
    	$this->format = $format;
    	 
    	return $this;
    }
    
    /**
     * Get storage
     * @return \Dms\Storage\StorageInterface
     */
    public function getStorage()
    {
        if (null === $this->storage) {
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
     * @return \Zend\ServiceManager\ServiceLocatorInterfac
     */
    public function getServiceLocator()
    {
        return $this->serviceLocator;
    }

    /**
     * Set service locator
     *
     * @param \Zend\ServiceManager\ServiceLocatorInterface $serviceLocator
     */
    public function setServiceLocator(ServiceLocatorInterface $serviceLocator)
    {
        $this->serviceLocator = $serviceLocator;

        return $this;
    }

    public function clear()
    {
    	$this->size = null;
    	$this->format = null;
        $this->document = null;
        $this->storage = null;
    }
}
