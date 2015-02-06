<?php

namespace Dms\Storage;

use Zend\ServiceManager\ServiceManagerAwareInterface;

interface StorageInterface extends ServiceManagerAwareInterface
{
    public function write(\Dms\Document\Document $document);
    public function exist(\Dms\Document\Document $document);
    public function read(\Dms\Document\Document &$document, $type = null, $print=false);
}
