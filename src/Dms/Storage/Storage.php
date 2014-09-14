<?php

namespace Dms\Storage;

use Zend\File\Transfer\Adapter\Http;
use Dms\Document\Document;

class Storage extends AbstractStorage
{
    public function write($data, $name, $support = Document::SUPPORT_DATA_STR)
    {
        $ret = null;
        $nameMod = substr($name, 4);
        $path = $this->options->getPath() . substr($name, 0, 2) . '/' . substr($name, 2, 2) . '/';
        if (!is_dir($path)) {
            mkdir($path,0777,true);
        }
        if ($support===Document::SUPPORT_FILE_MULTI_PART_STR) {
             $adp = new Http();
             $adp->setDestination($path);
             $adp->addFilter('Rename', array('target' => $nameMod));
             $ret = $adp->receive($data['name']);
        } else {
            $fp = fopen($path . $nameMod, 'w');
            $ret = fwrite($fp, $data);
            fclose($fp);
        }
        $this->getEventManager()->trigger(__FUNCTION__,$this,array('path' => $path,'short_name' => $nameMod, 'all_path' => $path . $nameMod,'support' => $support, 'name' => $name));

        return $ret;
    }

    public function read($token)
    {
        $content = null;
        $filename = $this->options->getPath() . '/' . substr($token, 0, 2) . '/' . substr($token, 2, 2) . '/' . substr($token, 4);

        if (file_exists($filename)) {
            $content = file_get_contents($filename);
        } else {
            $filename = $this->options->getPath() . '/' . $token;
            if (file_exists($filename)) {
                $content = file_get_contents($filename);
            }
        }

        return $content;
    }
}
