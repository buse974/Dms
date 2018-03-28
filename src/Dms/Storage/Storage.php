<?php
/**
 * github.com/buse974/Dms (https://github.com/buse974/Dms).
 *
 * Storage.php
 */
namespace Dms\Storage;

use Dms\Document\Document;
use Zend\Form\Form;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\FileInput;
use Aws\S3\S3Client;
use Google\Cloud\Storage\StorageClient;

/**
 * Class Storage.
 */
class Storage extends AbstractStorage
{
    private $s3Client;
    /**
     * If Path Is init.
     *
     * @var bool
     */
    private $init_path = false;

    /**
     * {@inheritdoc}
     *
     * @param \Dms\Document\Document $document
     *
     * @see \Dms\Storage\StorageInterface::write()
     */
    public function write(\Dms\Document\Document $document)
    {
        $ret = null;
        $conf_storage = $this->options->getStorage();
        $name = $document->getId();
        $nameMod = substr($name, 4);
        $f = substr($name, 0, 2).'/'.substr($name, 2, 2).'/';

        $path=$this->getBasePath().$f;
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
            if ($form->isValid()) {
                $form->getData();
            }
        } else if ($document->getSupport() === Document::SUPPORT_FILE_BUCKET_STR) {
            $bucket = $this->client->bucket($conf_storage['bucket_upload']);
            $object = $bucket->object($document->getDatas());
            $object->copy($conf_storage['bucket'], ['name' => $f.$nameMod.'.dat']);
        } else {
            $fp = fopen($p, 'w');
            fwrite($fp, $document->getDatas());
            $document->setWeight(strlen($document->getDatas()));
            fclose($fp);
        }
        
        if (isset($conf_storage['name']) && $conf_storage['name'] === 's3') {
          $this->client->copyObject([
              'Bucket' => $conf_storage['bucket'],
              'Key' => $f.$nameMod.'.dat',
              'CopySource' => $conf_storage['bucket'].'/'.$f.$nameMod.'.dat',
              'ContentType' => $document->getType(),
              'CacheControl' => 'public, max-age=31536000',
              'ContentDisposition' => sprintf('attachment;filename="%s"', str_replace(' ', '_', ((null === $document->getName()) ? (substr($file, -1 * strlen($document->getFormat())) === $document->getFormat()) ? $file : $file.'.'.$document->getFormat() : $document->getName()))),
              'MetadataDirective' => 'REPLACE',
          ]);
        } elseif (isset($conf_storage['name']) && $conf_storage['name'] === 'gs') {
          $bucket = $this->client->bucket($conf_storage['bucket']);
          $obj = $bucket->object($f.$nameMod.'.dat');
          $obj->update([
            'contentType' => $document->getType(),
            'CacheControl' => 'public, max-age=31536000',
            'contentDisposition' => sprintf('attachment;filename="%s"', ((null === $document->getName()) ? (substr($file, -1 * strlen($document->getFormat())) === $document->getFormat()) ? $file : $file.'.'.$document->getFormat() : $document->getName())),
          ]);
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
     * {@inheritdoc}
     *
     * @param \Dms\Document\Document &$document
     * @param string type
     * @param bool $print
     *
     * @see \Dms\Storage\StorageInterface::read()
     */
    public function read(\Dms\Document\Document &$document, $type = null, $print = null)
    {
        return (null === $type || $type !== 'data') ? $this->_readInf($document) : $this->_readData($document, $print);
    }

    /**
     * {@inheritdoc}
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
     * {@inheritdoc}
     *
     * @param \Dms\Document\Document $document
     * @param string                 $ext
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
     * Read Data.
     *
     * @param \Dms\Document\Document $document
     * @param bool|array             $print
     */
    public function _readData(\Dms\Document\Document &$document, $print = null)
    {
        $filename = $this->getPath($document, '.dat');
        if ($print !== null) {
            $handle = fopen($filename, 'r', false, stream_context_create(['s3' => ['seekable' => true]]));
            if (is_array($print)) {
                $start = (!empty($print['start']) ? $print['start'] : 0);
                fseek($handle, $start);
            }
            while (!feof($handle)) {
                echo fread($handle, 1024);
            }
            fclose($handle);
            exit();
        } else {
            $content = file_get_contents($filename);
            $document->setDatas($content);
        }
    }

    /**
     * Read Inf.
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
        $data = unserialize($content);
        $document->setSize($data->getSize());
        $document->setName($data->getName());
        $document->setType($data->getType());
        $document->setDescription($data->getDescription());
        $document->setEncoding($data->getEncoding());
        $document->setSupport($data->getSupport());
        $document->setWeight($data->getWeight());
        $document->setFormat($data->getFormat());
    }

    /**
     * Get Path Base.
     *
     * @return string
     */
    private function getBasePath()
    {
        $conf_storage = $this->options->getStorage();
        if (isset($conf_storage['name'])) {
          if ($conf_storage['name'] === 's3') {
            if ($this->init_path === false) {
              $this->client = new S3Client($conf_storage['options']);
              $this->client->registerStreamWrapper();
              $init_path = true;
            }
            $path = sprintf('s3://%s/', $conf_storage['bucket']);
          } elseif ($conf_storage['name'] === 'gs') {
            if ($this->init_path === false) {
              
              if(!empty($conf_storage['credentials_file'])) {
                  putenv('GOOGLE_APPLICATION_CREDENTIALS='.$conf_storage['credentials_file']);
              }
              $this->client = new StorageClient($conf_storage['options']);
              $this->client->registerStreamWrapper();
              $init_path = true;
            }
            $path = sprintf('gs://%s/', $conf_storage['bucket']);
          }
        } else {
            $path = $this->options->getPath();
        }

        return $path;
    }
}
