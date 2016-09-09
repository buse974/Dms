<?php
/**
 * github.com/buse974/Dms (https://github.com/buse974/Dms).
 *
 * DmsService.php
 */
namespace Dms\Service;

use Zend\ProgressBar\Upload\SessionProgress as Sp;
use Dms\Document\Manager;
use Dms\Document\NoFileException;

/**
 * Class DmsService.
 */
class DmsService
{
    /**
     * Document Manager.
     *
     * @var \Dms\Document\Manager
     */
    protected $document_manager;

    /**
     * Constructor.
     *
     * @param \Dms\Document\Manager $document_manager
     */
    public function __construct(\Dms\Document\Manager $document_manager)
    {
        $this->document_manager = $document_manager;
    }

    /**
     * Add Document.
     *
     * @param array $document
     *
     * @return string token
     */
    public function add(array $document)
    {
        $this->document_manager->createDocument($document);
        $this->document_manager->decode();
        $this->document_manager->writeFile();
        $document = $this->document_manager->getDocument();

        return $document->getId();
    }

    /**
     * Resize Document.
     *
     * @param array $document
     *
     * @return string token
     */
    public function resize($size)
    {
        $document = $this->document_manager->getDocument();

        $this->document_manager->setSize($size);
        $this->document_manager->writeFile($document->getId().'-'.$size);
        $document = $this->document_manager->getDocument();

        return $document->getId();
    }

    /**
     * Progress Action.
     *
     * @param string $id
     */
    public static function progressAction($id)
    {
        $progress = new Sp();

        return  $progress->getProgress($id);
    }

    /**
     * Print or get Document.
     *
     * @param string $file
     */
    public function get($file)
    {
        $document = null;
        try {
            $document = $this->document_manager->loadDocument($file)->getDocument();
        } catch (NoFileException $e) {
            preg_match('/(?P<id>\w+)($)?(-(?P<size>\w+)($)?)?(\[(?P<page>\d+)\]($)?)?(.*\.(?P<fmt>\w+)$)?/', $file, $matches, PREG_OFFSET_CAPTURE);
            $this->document_manager->loadDocument($matches['id'][0]);
            $this->document_manager->setSize((isset($matches['size']) && !empty($matches['size'][0])) ? $matches['size'][0] : null);
            $this->document_manager->setPage((isset($matches['page']) && !empty($matches['page'][0])) ? $matches['page'][0] : null);
            $this->document_manager->setFormat((isset($matches['fmt']) && !empty($matches['fmt'][0])) ? $matches['fmt'][0] : null);
            $document = $this->document_manager->writeFile($file)->getDocument();
        }

        if (null !== $document) {
            header('HTTP/1.0 200');
            header('Content-type: '.((null !== $document->getType()) ? $document->getType() : 'application/octet-stream'));
            header('Content-Transfer-Encoding: '.$document->getEncoding());
            header('Content-Disposition: '.sprintf('filename=%s', ((null === $document->getName()) ? (substr($file, -1 * strlen($document->getFormat())) === $document->getFormat()) ? $file : $file.'.'.$document->getFormat() : $document->getName())));
            header('Accept-Ranges: bytes');
            $print = true;
            if (isset($_SERVER['HTTP_RANGE'])) {
                $print = array();
                $range = $_SERVER['HTTP_RANGE'];
                $pieces = explode('=', $range);
                $seek = explode('-', trim($pieces[1]));
                $print['start'] = intval($seek[0]);
                $print['end'] = intval((!empty($seek[1]) ? $seek[1] : ($document->getWeight() - 1)));
                $size = ($print['end'] - $print['start'] + 1);
                header('HTTP/1.0 206');
                header('content-length: '.$size);
                header('Content-Range: bytes '.$print['start'].'-'.$print['end'].'/'.$document->getWeight());
            } else {
                header('content-length: '.$document->getWeight());
            }

            $document->getDatas($print);
        }
    }

    /**
     * Get Document.
     *
     * @param string $file
     *
     * @return \Dms\Document\Document
     */
    public function getDocument($file)
    {
        return $this->document_manager->loadDocument($file)->getDocument();
    }

    /**
     * Get Info Document.
     *
     * @param string $file
     * @param string $type
     *
     * @return string
     */
    public function getInfo($file, $type)
    {
        $content = '';
        $m_document = $this->document_manager->loadDocument($file)->getDocument();
        if ($m_document) {
            switch ($type) {
                case 'type':
                    $type = $m_document->getType();
                    $content = (empty($type) ? $m_document->getFormat() : $type);
                    break;
                case 'format':
                    $content = $m_document->getFormat();
                    break;
                case 'name':
                    $content = $m_document->getName();
                    break;
                case 'description':
                    $content = $m_document->getDescription();
                    break;
            }
        }

        return $content;
    }
}
