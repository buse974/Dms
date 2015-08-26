<?php

namespace Dms\Storage;

use Zend\File\Transfer\Adapter\Http;
use Dms\Document\Document;

class Storage extends AbstractStorage
{
    public function write(\Dms\Document\Document $document)
    {
        $ret = null;
        $name = $document->getId();

        $nameMod = substr($name, 4);
        $path = $this->options->getPath().'/'.substr($name, 0, 2).'/'.substr($name, 2, 2).'/';
        if (!is_dir($path)) {
            mkdir($path, 0777, true);
        }

        if ($document->getSupport() === Document::SUPPORT_FILE_MULTI_PART_STR) {
            $adp = new Http();
            $adp->setDestination($path);
            $adp->addFilter('Rename', array('target' => $nameMod.'.dat'));
            $adp->receive($document->getDatas()['name']);
        } else {
            $p = str_replace('//', '/', $path.$nameMod.'.dat');
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

    public function read(\Dms\Document\Document &$document, $type = null, $print = null)
    {
        return (null === $type || $type != 'datas') ? $this->_readInf($document) : $this->_readData($document, $print);
    }

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

    public function exist(\Dms\Document\Document $document)
    {
        try {
            $this->getPath($document, '.inf');

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function getPath(\Dms\Document\Document $document, $ext = '')
    {
        $name = $document->getId().$ext;

        $filename = $this->options->getPath().substr($name, 0, 2).'/'.substr($name, 2, 2).'/'.substr($name, 4);

        if (!file_exists($filename)) {
            syslog(1, $filename);
            $filename = $this->options->getPath().'/'.$name;
            if (!file_exists($filename)) {
                syslog(1, $filename);
                throw new \Exception('no file');
            }
        }

        return $filename;
    }

    public function _readData(\Dms\Document\Document &$document, $print = null)
    {
        $content = null;

        $filename = $this->getPath($document, '.dat');

        $handle = fopen($filename, 'r');
        $size = filesize($filename);

        
        if (is_array($print)) {
            $start = (!empty($print['start']) ? $print['start'] : 0);
            $end = (!empty($print['end']) ? $print['end'] : $size);
            $size = ($end - $start + 1);
            fseek($handle, $start);
        }

        while ($size) {
            $read = ($size > 8192) ? 8192 : $size;
            $size -= $read;
            if ($print !== null) {
                print(fread($handle, $read));
            } else {
                $content .= fread($handle, $read);
            }
        }
        fclose($handle);

        if ($print !== null) {
            syslog(1, 'EXIT PRINT');
            exit();
        }

        $document->setDatas($content);
    }
}
