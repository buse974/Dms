<?php

namespace DmsTest\Document;

use \PHPUnit_Framework_TestCase;
use Dms\Document\Document;

class DocumentTest extends PHPUnit_Framework_TestCase
{
    public function testCanGetId()
    {
        $document = new Document();

        $this->assertEquals(strlen($document->getId()),40);
    }

    public function testCanSerialize()
    {
        $datas = 'body document';

        $document = new Document();
        $document->setDatas($datas)
                 ->setDescription('description document')
                 ->setEncoding('binary')
                 ->setSupport('data')
                 ->setName('file')
                 ->setId('id')
                 ->setSize('300x200')
                 ->setType('jpg');

        $serialize = serialize($document);
        $this->assertEquals($serialize, 'C:21:"Dms\Document\Document":216:{a:8:{s:2:"id";s:10:"id-300x200";s:4:"size";s:7:"300x200";s:4:"name";s:4:"file";s:4:"type";s:3:"jpg";s:11:"description";s:20:"description document";s:8:"encoding";s:6:"binary";s:7:"support";s:4:"data";s:6:"weight";N;}}');
    }

    public function testCanunserialize()
    {
        $serialize = 'C:21:"Dms\Document\Document":201:{a:7:{s:2:"id";s:10:"id-300x200";s:4:"size";s:7:"300x200";s:4:"name";s:4:"file";s:4:"type";s:3:"jpg";s:11:"description";s:20:"description document";s:8:"encoding";s:6:"binary";s:7:"support";s:4:"data";}}';
        $document = unserialize($serialize);

        $data = $document->getDatas();
        $this->assertEquals(empty($data),true);
        $this->assertEquals($document->getDescription(),'description document');
        $this->assertEquals($document->getEncoding(),'binary');
        $this->assertEquals($document->getId(),'id-300x200');
        $this->assertEquals($document->getName(),'file');
        $this->assertEquals($document->getSupport(),'data');
        $this->assertEquals($document->getSize(),'300x200');
        $this->assertEquals($document->getType(),'jpg');
    }

    public function testCanGetEncodingDefault()
    {
        $document = new Document();
        $enc = $document->getEncoding();

        $this->assertEquals($enc,'binary');
    }

    public function testCanGetSupportDefault()
    {
        $document = new Document();
        $sup = $document->getSupport();

        $this->assertEquals($sup,'data');
    }

    public function testCanGetIdAfterSetSize()
    {
        $datas = 'body document';

        $document = new Document();
        $document->setDatas($datas);
        $this->assertEquals(strlen($document->getId()),40);
        $document->setSize('300x200');
        $this->assertEquals(strlen($document->getId()),40+strlen('-300x200'));
    }

    public function testCanSetIdWithSize()
    {
        $id = 'id1234-300x300';

        $document = new Document();
        $document->setId($id);
        $this->assertEquals($document->getId(),'id1234-300x300');
        $this->assertEquals($document->getSize(),'300x300');
    }
}
