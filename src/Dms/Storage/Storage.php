<?php
/**
 * 
 * github.com/buse974/Dms (https://github.com/buse974/Dms)
 *
 * Storage.php
 *
 */
namespace Dms\Storage;

use Dms\Document\Document;
use Zend\Form\Form;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\FileInput;
use Zend\Form\Element\File;
use Aws\S3\S3Client;

/**
 * Class Storage
 */
class Storage extends AbstractStorage
{
    /**
     * If Path Is init
     * 
     * @var bool
     */
    private $init_path = false;
    
    /**
     * 
     * {@inheritDoc}
     * 
     * @param \Dms\Document\Document $document
     * 
     * @see \Dms\Storage\StorageInterface::write()
     */
    public function write(\Dms\Document\Document $document)
    {
        $ret = null;
        $name = $document->getId();
        $nameMod = substr($name, 4);
        $path = $this->getBasePath().substr($name, 0, 2).'/'.substr($name, 2, 2).'/';
        if (!is_dir($path)) {
            mkdir($path, 0777, true);
        }
        
        $p = $path.$nameMod.'.dat';
        if ($document->getSupport() === Document::SUPPORT_FILE_MULTI_PART_STR) {
            $fileInput = new FileInput(key($document->getDatas()));
            $fileInput->getFilterChain()->attachByName('filerenameupload', ['target' => $p]);
            
            $inputFilter = new InputFilter();
            $inputFilter->add($fileInput);
         
            $form = new Form();
            $form->setInputFilter($inputFilter);
            $form->setData($document->getDatas());
            if($form->isValid()) {
                $form->getData();
            }
        } else {
            $fp = fopen($p, 'w');
            fwrite($fp, $document->getDatas());
            $document->setWeight(strlen($document->getDatas()));
            fclose($fp);
        }

        $document->setSupport(Document::SUPPORT_FILE_STR);
        $this->getEventManager()->trigger(__FUNCTION__, $this, array('path' => $path, 'short_name' => $nameMod, 'all_path' => $path.$nameMod.'.dat', 'support' => $document->getSupport(), 'name' => $name));

        $serialize = serialize($document);
        $fp = fopen($path.$nameMod.'.inf', 'w');
        $ret += fwrite($fp, $serialize);
        fclose($fp);
        $this->getEventManager()->trigger(__FUNCTION__, $this, array('path' => $path, 'short_name' => $nameMod, 'all_path' => $path.$nameMod.'inf', 'support' => $document->getSupport(), 'name' => $name));

        return $ret;
    }

    /**
     * 
     * {@inheritDoc}
     * 
     * @param \Dms\Document\Document &$document
     * @param string type
     * @param bool $print
     * 
     * @see \Dms\Storage\StorageInterface::read()
     */
    public function read(\Dms\Document\Document &$document, $type = null, $print = null)
    {
        return (null === $type || $type !== 'datas') ? $this->_readInf($document) : $this->_readData($document, $print);
    }

    /**
     * 
     * {@inheritDoc}
     * 
     * @param \Dms\Document\Document $document
     * 
     * @see \Dms\Storage\StorageInterface::exist()
     */
    public function exist(\Dms\Document\Document $document)
    {
        try {
            $this->getPath($document, '.inf');
        } catch (\Exception $e) {
            return false;
        }
        
        return true;
    }

    /**
     * 
     * {@inheritDoc}
     * 
     * @param \Dms\Document\Document $document
     * @param string $ext
     * 
     * @see \Dms\Storage\StorageInterface::getPath()
     */
    public function getPath(\Dms\Document\Document $document, $ext = '')
    {
        $name = $document->getId().$ext;
        $filename = $this->getBasePath().substr($name, 0, 2).'/'.substr($name, 2, 2).'/'.substr($name, 4);
        if (!file_exists($filename)) {
            throw new \Exception('no file');
        }

        return $filename;
    }

    /**
     * Read Data
     *  
     * @param \Dms\Document\Document $document
     * @param bool|array $print
     */
    public function _readData(\Dms\Document\Document &$document, $print = null)
    {
        $filename = $this->getPath($document, '.dat');
        if($print !== null) {
            $handle = fopen($filename, 'r');
            if (is_array($print)) {
                $start = (!empty($print['start']) ? $print['start'] : 0);
                //$end = (!empty($print['end']) ? $print['end'] : filesize($filename));
                fseek($handle, $start);
            }
            while (!feof($handle)) {
                print(fread($handle, 8192));
            }
            fclose($handle);
            exit();
        } else {
            $content = file_get_contents($filename);
            $document->setDatas($content);
        }
    }
    
    /**
     * Read Inf
     * 
     * @param \Dms\Document\Document $document
     */
    public function _readInf(\Dms\Document\Document &$document)
    {
        $content = null;
        $filename = $this->getPath($document, '.inf');
        
        $handle = fopen($filename, 'r');
        $size = filesize($filename);
        
        while ($size) {
            $read = ($size > 8192) ? 8192 : $size;
            $size -= $read;
            $content .= fread($handle, $read);
        }
        
        fclose($handle);
        $datas = unserialize($content);
        $document->setSize($datas->getSize());
        $document->setName($datas->getName());
        $document->setType($datas->getType());
        $document->setDescription($datas->getDescription());
        $document->setEncoding($datas->getEncoding());
        $document->setSupport($datas->getSupport());
        $document->setWeight($datas->getWeight());
        $document->setFormat($datas->getFormat());
    }
    
    /**
     * Get Path Base
     * 
     * @return string
     */
    private function getBasePath()
    {
        $conf_storage = $this->options->getStorage();
        if(isset($conf_storage['name']) && $conf_storage['name'] === 's3') {
            if($this->init_path === false) {
                $s3Client = new S3Client($conf_storage['options']);
                $s3Client->registerStreamWrapper();
                $init_path = true;
            }
            $path = sprintf("s3://%s/",$conf_storage['bucket']);
        } else {
            $path = $this->options->getPath();
        }
        
        return $path;
    }
}
