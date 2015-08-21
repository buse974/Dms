<?php
namespace Dms\Document;

use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Dms\Storage\StorageInterface;
use Dms\Convert\Convert;
use Dms\Resize\Resize;
use Dms\FFmpeg\FFmpeg;

class Manager implements ServiceLocatorAwareInterface
{

    /**
     *
     * @var \Dms\Document\Document
     */
    protected $document;

    /**
     *
     * @var \Dms\Document\Document
     */
    protected $new_document;

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
     * Load document info
     *
     * @param string|\Dms\Document\Document $document            
     * @throws \Exception
     * @return \Dms\Document\Manager
     */
    public function loadDocument($document)
    {
        $this->clear();
        $this->document = $document;
        if (! $document instanceof Document && is_string($document)) {
            $this->document = new Document();
            $this->document->setId($document);
        }
        
        $this->document->setStorage($this->getStorage());
        
        if (! empty($this->document->getId()) && ! $this->document->exist()) {
            $this->clear();
            throw new \Exception('Param is not id: ' . $document);
        }
        
        return $this;
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
            ->setType((isset($document['type'])) ? $document['type'] : null)
            ->setSupport((isset($document['support'])) ? $document['support'] : null)
            ->setName((isset($document['name'])) ? $document['name'] : null)
            ->setSize((isset($document['size'])) ? $document['size'] : null)
            ->setFormat((isset($document['format'])) ? $document['format'] : null)
            ->setWeight((isset($document['weight'])) ? $document['weight'] : null);
        
        $this->document->setStorage($this->getStorage());
        
        return $this;
    }

    /**
     * Record a document to the Dms
     *
     * @return \Dms\Document\Manager
     */
    public function writeFile($id = null)
    {
        if (null === $this->document) {
            throw new \Exception('Document does not exist');
        }
        
        $obj_mime_type = new MimeType();
        $is_video = ((strpos($obj_mime_type->getMimeTypeByExtension($this->document->getFormat()), 'video') === 0) || (strpos($this->document->getType(), 'video') === 0));
        if ($is_video && (null !== $this->getFormat() || null !== $this->getSize() || null !== $this->getPage())) {
            $this->createPicture();
            $this->document = $this->new_document;
            $this->new_document = null;
        }
        
        // si que resize
        //
        // si format n'est pas une image ou IN non compatible
        // convertire d'abort avec uniconv en format compatible imagick puis par defaut mettre un numéro de page 1 si non existant
        // puis resize imagick avec format de sortie par default (jpeg)
        if (null !== $this->getSize() && null === $this->getFormat()) {
            // si format n'est pas une image ou IN non compatible
            
            $is_img = (strpos($obj_mime_type->getMimeTypeByExtension($this->document->getFormat()), 'image') === 0);
            if ($is_img && Resize::isCompatible($this->document->getFormat())) {
                $this->resize();
            } else { // convertire d'abort avec uniconv en format compatible imagick puis par defaut mettre un numéro de page 1 si non existant
                if (! $is_img && $this->page == null) {
                    $this->setPage(1);
                }
                $this->setFormat('jpg');
                $this->convert();
                $this->resize();
            }
            // si que format
            //
            // vérifier que le format n'est pas le même.
            // sinonuniconv (voir imagmagick selon le suport est quelité)
            //
        } elseif (null === $this->getSize() && null !== $this->getFormat()) {
            if ($this->getFormat() !== $this->getDocument()->getFormat()) {
                $obj_mime_type = new MimeType();
                $is_img = (strpos($obj_mime_type->getMimeTypeByExtension($this->document->getFormat()), 'image') === 0);
                if (! $is_img) {
                    $this->setPage(1);
                }
                $this->convert();
            }
            // si resize + format
        } elseif (null !== $this->getSize() && null !== $this->getFormat()) {
            // si format compatible IN et OUT avec imagik on utilise imagik pour les deux
            if (Resize::isCompatible($this->format) && Resize::isCompatible($this->getDocument()->getFormat())) {
                $this->resize();
                $this->getNewDocument()->setId($id);
                $document_write = $this->getNewDocument();
                // si format IN et OUT non compatible avec Imagick
                // Convertion avec uniconv en format compatible Imagick (jpg)
                // Resize avec imagick
                // Convertion OUT avec uniconv
            } elseif (! Resize::isCompatible($this->format) && ! Resize::isCompatible($this->getDocument()->getFormat())) {
                $tmp_fmt = $this->getFormat();
                $this->setFormat('jpg');
                $obj_mime_type = new MimeType();
                $is_img = (strpos($obj_mime_type->getMimeTypeByExtension($this->document->getFormat()), 'image') === 0);
                if (! $is_img) {
                    $this->setPage(1);
                }
                $this->convert();
                $this->resize();
                $this->setFormat($tmp_fmt);
                $this->convert();
                // si que format IN compatible Imagick
                // resize avec imagick (jpg)
                // convertie uniconv
            } elseif (! Resize::isCompatible($this->format) && Resize::isCompatible($this->getDocument()->getFormat())) {
                $tmp_fmt = $this->getFormat();
                $this->setFormat('jpg');
                $this->resize();
                $this->setFormat($tmp_fmt);
                $this->convert();
                // si que OUT compatible
                // convertie uniconv en (jpeg)
                // resize et format avec imagick
            } elseif (Resize::isCompatible($this->format) && ! Resize::isCompatible($this->getDocument()->getFormat())) {
                $tmp_fmt = $this->getFormat();
                $this->setFormat('jpg');
                $obj_mime_type = new MimeType();
                $is_img = (strpos($obj_mime_type->getMimeTypeByExtension($this->document->getFormat()), 'image') === 0);
                if (! $is_img) {
                    $this->setPage(1);
                }
                $this->convert();
                $this->setFormat($tmp_fmt);
                $this->resize();
            }
        }
        
        if ($this->new_document !== null) {
            $this->document = $this->new_document;
        }
        
        if (null !== $id) {
            $this->document->setId($id);
        }
        
        $this->document->setStorage($this->getStorage());
        $this->document->write();
        
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
        
        if ($this->document->getEncoding() != Document::TYPE_BINARY_STR && $this->document->getSupport() == Document::SUPPORT_DATA_STR) {
            $this->document->setDatas($this->getEncDec($this->document->getEncoding())
                ->decode($this->document->getDatas()));
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
        $resize = $this->getServiceResize();
        $resize->setData($this->getDocument()
            ->getDatas())
            ->setFormat($this->getFormat());
        $this->getNewDocument()->setEncoding(Document::TYPE_BINARY_STR);
        $this->getNewDocument()->setDatas($resize->getResizeData($this->size));
        $this->getNewDocument()->setSize($this->size);
        $this->getNewDocument()->setFormat($resize->getFormat());
        $this->getNewDocument()->setPage($this->getPage());
        
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
            ->setTmp($this->getServiceLocator()
            ->get('Config')['dms-conf']['convert']['tmp'])
            ->setPage($this->getPage());
        
        $this->getNewDocument()->setDatas($convert->getConvertData($this->getFormat()));
        $this->getNewDocument()->setEncoding(Document::TYPE_BINARY_STR);
        $this->getNewDocument()->setFormat($this->getFormat());
        $this->getNewDocument()->setPage($this->getPage());
    }

    /**
     * Convert format file
     */
    private function createPicture()
    {
        $ff = new FFmpeg();
        $ff->setFile($this->document->getPathDat());
        $this->getNewDocument()->setDatas($ff->getPicture((($this->getPage() !== null) ? $this->getPage() : 50)));
        $this->getNewDocument()->setEncoding(Document::TYPE_BINARY_STR);
        $this->getNewDocument()->setFormat($ff->getFormat());
        $this->getNewDocument()->setSize($ff->getSize());
        $this->getNewDocument()->setType($ff->getTypeMine());
        $this->getNewDocument()->setName($this->document->getName());
        $this->getNewDocument()->setWeight(strlen($this->getNewDocument()
             ->getDatas()));
        
        $this->setPage(null);
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
     * Get New Document
     *
     * @return \Dms\Document\Document
     */
    public function getNewDocument()
    {
        if (null === $this->new_document) {
            $this->new_document = new Document();
            if(null !== $this->document) {
                $this->new_document->setName($this->document->getName());
            }
        }
        
        return $this->new_document;
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
     *
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
     *
     * @param \Dms\Storage\StorageInterface $storage            
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
     *
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
        $this->document = null;
        $this->new_document = null;
        $this->size = null;
        $this->format = null;
        $this->document = null;
        $this->storage = null;
        $this->page = null;
    }
}
