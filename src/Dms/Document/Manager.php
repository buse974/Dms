<?php

namespace Dms\Document;

use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Dms\Storage\StorageInterface;
use Dms\Convert\Convert;
use Dms\Convert\Exception\ConvertException;
use Dms\Resize\Resize;

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
     * @var number
     */
    protected $page;

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
     * @param  string|\Dms\Document\Document $document
     * @throws \Exception
     * @return \Dms\Document\Manager
     */
    public function loadDocument($document)
    {
        if ($document instanceof Document && is_string($document->getId())) {
            $document = $document->getId();
        }
        if (!is_string($document)) {
            throw new \Exception('Param is not id: ' . $document);
        }

        $info = $this->getStorage()->read($document .'.inf');

        if (!$info) {
            throw new \Exception('Not document: ' . $document);
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
     * @param  string|\Dms\Document\Document $document
     * @throws \Exception
     * @return \Dms\Document\Manager
     */
    public function loadDocumentInfo($document)
    {
        if ($document instanceof Document && is_string($document->getId())) {
            $document = $document->getId();
        }
        if (!is_string($document)) {
            throw new \Exception('Param is not id: ' . $document);
        }

        $info = $this->getStorage()->read($document .'.inf');

        if (!$info) {
            throw new \Exception('Not document: ' . $document);
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
        if (null === $this->document) {
            $this->document = new Document();
        }

        return $this->document;
    }

    /**
     * Initialise Document with a array
     *
     * @param  array                 $document
     * @return \Dms\Document\Manager
     */
    public function createDocument(array $document)
    {
        $this->clear();
        $this->document = new Document();
        $this->document->setId((isset($document['id'])) ? $document['id'] : null)
                       ->setDatas((isset($document['data'])) ? $document['data'] : null)
                       ->setEncoding((isset($document['coding'])) ? $document['coding'] : null)
                       ->setType((isset($document['type'])) ? $document['type'] : null)
                       ->setSupport((isset($document['support'])) ? $document['support'] : null)
                       ->setName((isset($document['name'])) ? $document['name'] : null)
                       ->setSize((isset($document['size'])) ? $document['size'] : null)
                       ->setFormat((isset($document['format'])) ? $document['format'] : null)
                       ->setWeight((isset($document['weight'])) ? $document['weight'] : null);

        return $this;
    }

    /**
     * Record a document to the Dms
     *
     * @return \Dms\Document\Manager
     */
    public function writeFile($id=null)
    {
        if (null === $this->document) {
            throw new \Exception('Document does not exist');
        }
        if (null !== $id) {
            $this->getDocument()->setId($id);
        }
        
        //si que resize
        //
        // si format n'est pas une image ou IN non compatible
        // convertire d'abort avec uniconv en format compatible imagick puis par defaut mettre un numéro de page 1 si non existant
        // puis resize imagick avec format de sortie par default (jpeg)
        if (null!== $this->getSize() && null===$this->getFormat()) {
        	//si format n'est pas une image ou IN non compatible
        	$obj_mime_type = new MimeType();
        	$is_img = (strpos($obj_mime_type->getMimeTypeByExtension($this->document->getFormat()), 'image') === 0);
        	if($is_img && Resize::isCompatible($this->document->getFormat())) {
        		$this->resize();
        	} else { //convertire d'abort avec uniconv en format compatible imagick puis par defaut mettre un numéro de page 1 si non existant
        		if(!$is_img && $this->page == null) {
        			$this->setPage(1);
        		}
        		$this->setFormat('jpg');
        		$this->convert();
        		$this->resize();
        	}
        //si que format
        //
        // vérifier que le format n'est pas le même.
        // sinonuniconv (voir imagmagick selon le suport est quelité)
        //
        } elseif (null=== $this->getSize() && null!==$this->getFormat()) {
        	if($this->getFormat() !== $this->getDocument()->getFormat()) {
        		$obj_mime_type = new MimeType();
        		$is_img = (strpos($obj_mime_type->getMimeTypeByExtension($this->document->getFormat()), 'image') === 0);
        		if(!$is_img) {
        			$this->setPage(1);
        		}
        		$this->convert();
        	}
        // si resize + format
        } elseif (null!== $this->getSize() && null!==$this->getFormat()) {
        	//   si format compatible IN et OUT avec imagik on utilise imagik pour les deux
        	if(Resize::isCompatible($this->format) && Resize::isCompatible($this->getDocument()->getFormat())) {
        		$this->resize();
        	//   si format IN et OUT non compatible avec Imagick
        	//		Convertion avec uniconv en format compatible Imagick (jpg)
        	//      Resize avec imagick
        	//      Convertion OUT avec uniconv
        	} elseif (!Resize::isCompatible($this->format) && !Resize::isCompatible($this->getDocument()->getFormat())) {
        		$tmp_fmt = $this->getFormat();
        		$this->setFormat('jpg');
        		$obj_mime_type = new MimeType();
        		$is_img = (strpos($obj_mime_type->getMimeTypeByExtension($this->document->getFormat()), 'image') === 0);
        		if(!$is_img) {
        			$this->setPage(1);
        		}
        		$this->convert();
        		$this->resize();
        		$this->setFormat($tmp_fmt);
        		$this->convert();
        	//   si que format IN compatible Imagick
        	//      resize avec imagick (jpg)
        	//      convertie uniconv
        	} elseif (!Resize::isCompatible($this->format) && Resize::isCompatible($this->getDocument()->getFormat())) {
        		$tmp_fmt = $this->getFormat();
        		$this->setFormat('jpg');
        		$this->resize();
        		$this->setFormat($tmp_fmt);
        		$this->convert();
        	//   si que OUT compatible
        	//      convertie uniconv en (jpeg)
        	//      resize et format avec imagick
        	} elseif (Resize::isCompatible($this->format) && !Resize::isCompatible($this->getDocument()->getFormat())) {
        		$tmp_fmt = $this->getFormat();
        		$this->setFormat('jpg');
        		$obj_mime_type = new MimeType();
        		$is_img = (strpos($obj_mime_type->getMimeTypeByExtension($this->document->getFormat()), 'image') === 0);
        		if(!$is_img) {
        			$this->setPage(1);
        		}
        		$this->convert();
        		$this->setFormat($tmp_fmt);
        		$this->resize();
        	}
        }
        
        $this->getStorage()->write($this->document->getDatas(), $this->document->getId() .'.dat',$this->document->getSupport());
        $this->document->setSupport(Document::SUPPORT_FILE_STR);
        $this->getStorage()->write(serialize($this->document), $this->document->getId() .'.inf');
        $this->document->setIsWrite(true);

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
     * @param  number                $size
     * @return \Dms\Document\Manager
     */
    private function resize()
    {
        $resize = $this->getServiceResize();
        $resize->setData($this->getDocument()->getDatas())
               ->setFormat($this->getFormat());
        $this->document->setEncoding(Document::TYPE_BINARY_STR);
        $this->document->setDatas($resize->getResizeData($this->size));
        $this->document->setSize($this->size);
        $this->document->setFormat($resize->getFormat());
        $this->document->setPage($this->getPage());

        return $this;
        
    }

    /**
     * Convert format file
     */
    private function convert()
    {
        $convert = new Convert();
        $convert->setData($this->document->getDatas())
                ->setFormat($this->document->getFormat())
                ->setTmp($this->getServiceLocator()->get('Config')['dms-conf']['convert']['tmp'])
                ->setPage($this->getPage());

        $this->document->setDatas($convert->getConvertData($this->getFormat()));
        $this->document->setEncoding(Document::TYPE_BINARY_STR);
        $this->document->setFormat($this->getFormat());
        $this->document->setPage($this->getPage());
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

    public function getPage()
    {
        return $this->page;
    }

    public function setPage($page)
    {
        $this->page = $page;

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
     * @return \Zend\ServiceManager\ServiceLocatorInterface
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
        $this->page = null;
    }
}
