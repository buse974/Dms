<?php
/**
 * github.com/buse974/Dms (https://github.com/buse974/Dms).
 *
 * Manager Dms
 */
namespace Dms\Document;

use Dms\Convert\Convert;
use Dms\Resize\Resize;
use Dms\FFmpeg\FFmpeg;
use Dms\Storage\Storage;
use Interop\Container\ContainerInterface;

/**
 * Class Manager.
 */
class Manager
{
    /**
     * Document.
     *
     * @var \Dms\Document\Document
     */
    protected $document;

    /**
     * New Document.
     *
     * @var \Dms\Document\Document
     */
    protected $new_document;

    /**
     * Format Out.
     *
     * @var string
     */
    protected $format;

    /**
     * Size Out.
     *
     * @var string
     */
    protected $size;

    /**
     * Page Out.
     *
     * @var int
     */
    protected $page;

    /**
     * Storage Object.
     *
     * @var \Dms\Storage\StorageInterface
     */
    protected $storage;

    /**
     * Option dms-conf.
     *
     * @var array
     */
    protected $option;

    /**
     * Container.
     *
     * @var ContainerInterface
     */
    protected $container;

    /**
     * Constructor.
     *
     * @param array              $option
     * @param ContainerInterface $container
     */
    public function __construct($option, $container)
    {
        $this->option = $option;
        $this->container = $container;
    }

    /**
     * Load document info.
     *
     * @param string|\Dms\Document\Document $document
     *
     * @throws \Exception
     *
     * @return \Dms\Document\Manager
     */
    public function loadDocument($document)
    {
        $this->clear();
        $this->document = $document;
        if (!$document instanceof Document && is_string($document)) {
            $this->document = new Document();
            $this->document->setId($document);
        }

        $this->document->setStorage($this->getStorage());

        if (!$this->document->exist()) {
            $this->clear();
            throw new NoFileException($document);
        }

        return $this;
    }

    /**
     * Initialise Document with a array.
     *
     * @param array $document
     *
     * @return \Dms\Document\Manager
     */
    public function createDocument(array $document)
    {
        $this->clear();
        $this->document = new Document();
        $this->document->isRead()
            ->setId((isset($document['id'])) ? $document['id'] : null)
            ->setDatas((isset($document['data'])) ? $document['data'] : null)
            ->setEncoding((isset($document['coding'])) ? $document['coding'] : null)
            ->setType((isset($document['type'])) ? $document['type'] : null)
            ->setSupport((isset($document['support'])) ? $document['support'] : null)
            ->setFormat((isset($document['format'])) ? $document['format'] : null)
            ->setName((isset($document['name'])) ? str_replace(';', '', str_replace(',', '', $document['name'])) : null)
            ->setSize((isset($document['size'])) ? $document['size'] : null)
            ->setWeight((isset($document['weight'])) ? $document['weight'] : null);

        $this->document->setStorage($this->getStorage());

        return $this;
    }

    protected function saveTmp($document, $id = null)
    {
        if (null !== $id) {
            $document->setId($id);
        }
        $document->setStorage($this->getStorage());
        $document->write();
    }
    
    /**
     * Record a document to the Dms.
     *
     * @param int $id
     *
     * @throws \Exception
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
        // si format n'est pas une image ou IN non compatible
        // convertire d'abort avec uniconv en format compatible imagick puis par defaut mettre un numéro de page 1 si non existant
        // puis resize imagick avec format de sortie par default (jpeg)
        if (null !== $this->getSize() && null === $this->getFormat()) {
            $this->saveTmp(clone $document, $id);
            // si format n'est pas une image ou IN non compatible
            $is_img = (strpos($obj_mime_type->getMimeTypeByExtension($this->document->getFormat()), 'image') === 0);
            if ($is_img && Resize::isCompatible($this->document->getFormat())) {
                $this->resize();
            } else { // convertire d'abort avec uniconv en format compatible imagick puis par defaut mettre un numéro de page 1 si non existant
                if (!$is_img && $this->page == null) {
                    $this->setPage(1);
                }
                $this->setFormat('jpg');
                $this->convert();
                $this->resize();
            }
        } elseif (null === $this->getSize() && null !== $this->getFormat()) {
        // si que format
        // vérifier que le format n'est pas le même.
        // sinon uniconv (voir imagmagick selon le suport est qualité)
            if ($this->getFormat() !== $this->getDocument()->getFormat()) {
                $this->saveTmp(clone $document, $id);
                $obj_mime_type = new MimeType();
                $is_img = (strpos($obj_mime_type->getMimeTypeByExtension($this->document->getFormat()), 'image') === 0);
                if (!$is_img) {
                    $this->setPage(1);
                }
                $this->convert();
            }
        } elseif (null !== $this->getSize() && null !== $this->getFormat()) {
            $this->saveTmp(clone $document, $id);
        // si resize + format
            if (Resize::isCompatible($this->format) && Resize::isCompatible($this->getDocument()->getFormat())) {
            // si format compatible IN et OUT avec imagik on utilise imagik pour les deux
                $this->resize();
            } elseif (!Resize::isCompatible($this->format) && !Resize::isCompatible($this->getDocument()->getFormat())) {
            // si format IN et OUT non compatible avec Imagick
            // Convertion avec uniconv en format compatible Imagick (jpg)
            // Resize avec imagick
            // Convertion OUT avec uniconv
                $tmp_fmt = $this->getFormat();
                $this->setFormat('jpg');
                $obj_mime_type = new MimeType();
                $is_img = (strpos($obj_mime_type->getMimeTypeByExtension($this->document->getFormat()), 'image') === 0);
                if (!$is_img) {
                    $this->setPage(1);
                }
                $this->convert();
                $this->resize();
                $this->setFormat($tmp_fmt);
                $this->convert();
            } elseif (!Resize::isCompatible($this->format) && Resize::isCompatible($this->getDocument()->getFormat())) {
            // si que format IN compatible Imagick
            // resize avec imagick (jpg)
            // convertie uniconv
                $tmp_fmt = $this->getFormat();
                $this->setFormat('jpg');
                $this->resize();
                $this->setFormat($tmp_fmt);
                $this->convert();
            } elseif (Resize::isCompatible($this->format) && !Resize::isCompatible($this->getDocument()->getFormat())) {
            // si que OUT compatible
            // convertie uniconv en (jpeg)
            // resize et format avec imagick
                $tmp_fmt = $this->getFormat();
                $this->setFormat('jpg');
                $obj_mime_type = new MimeType();
                $is_img = (strpos($obj_mime_type->getMimeTypeByExtension($this->document->getFormat()), 'image') === 0);
                if (!$is_img) {
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
     * Decode Document to binary.
     *
     * @throws \Exception
     *
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
     * Resize document.
     *
     * @param number $size
     *
     * @return \Dms\Document\Manager
     */
    private function resize()
    {
        $resize = new Resize(['allow' => $this->option['size_allowed'], 'active' => $this->option['check_size_allowed']]);
        $resize->setData($this->getDocument()->getDatas())->setFormat($this->getFormat());

        $this->getNewDocument()->setEncoding(Document::TYPE_BINARY_STR);
        $this->getNewDocument()->setDatas($resize->getResizeData($this->size));
        $this->getNewDocument()->setSize($this->size);
        $this->getNewDocument()->setFormat($resize->getFormat());
        $this->getNewDocument()->setPage($this->getPage());

        return $this;
    }

    /**
     * Convert format file.
     */
    private function convert()
    {
        $convert = new Convert();
        $convert->setData($this->document->getDatas())
            ->setFormat($this->document->getFormat())
            ->setTmp($this->option['convert']['tmp'])
            ->setPage($this->getPage());

        $this->getNewDocument()->setDatas($convert->getConvertData($this->getFormat()));
        $this->getNewDocument()->setEncoding(Document::TYPE_BINARY_STR);
        $this->getNewDocument()->setFormat($this->getFormat());
        $this->getNewDocument()->setPage($this->getPage());
    }

    /**
     * Convert format file.
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
     * Get Document.
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
     * Get New Document.
     *
     * @return \Dms\Document\Document
     */
    public function getNewDocument()
    {
        if (null === $this->new_document) {
            $this->new_document = new Document();
            if (null !== $this->document) {
                $this->new_document->setName($this->document->getName());
            }
        }

        return $this->new_document;
    }

    /**
     * Get Size.
     *
     * @return string
     */
    public function getSize()
    {
        return $this->size;
    }

    /**
     * Set Size.
     *
     * @param int $size
     *
     * @return \Dms\Document\Manager
     */
    public function setSize($size)
    {
        $this->size = $size;

        return $this;
    }

    /**
     * Get Format.
     *
     * @return string
     */
    public function getFormat()
    {
        return $this->format;
    }

    /**
     * Set Format.
     *
     * @param string $format
     *
     * @return \Dms\Document\Manager
     */
    public function setFormat($format)
    {
        $this->format = $format;

        return $this;
    }

    /**
     * Get num Page.
     *
     * @return int
     */
    public function getPage()
    {
        return $this->page;
    }

    /**
     * Set num Page.
     *
     * @param int $page
     *
     * @return \Dms\Document\Manager
     */
    public function setPage($page)
    {
        $this->page = $page;

        return $this;
    }

    /**
     * Get storage.
     *
     * @return \Dms\Storage\StorageInterface
     */
    public function getStorage()
    {
        if (null === $this->storage) {
            $this->storage = new Storage([
                'path' => $this->option['default_path'], 
                'storage' => $this->option['storage']]);
        }

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

    /**
     * Get Encoder/Decoder Object.
     *
     * @param string $enc
     *
     * @return \Dms\Coding\CodingInterface
     */
    public function getEncDec($enc)
    {
        return $this->container->get($enc.'Coding');
    }

    /**
     * Clear Manager.
     */
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
