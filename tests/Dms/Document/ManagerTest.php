<?php

namespace DmsTest\Document;

use DmsTest\bootstrap;
use \PHPUnit_Framework_TestCase;

class ManagerTest extends PHPUnit_Framework_TestCase
{
    public function tearDown()
    {
        $this->deleteDirRec(__DIR__ . '/../../_upload/00');
    }

    public function deleteDirRec($path)
    {
        foreach (glob($path . "/*") as $filename) {
            (!is_dir($filename)) ? unlink($filename) : $this->deleteDirRec($filename);
        }
        if (is_dir($path)) {
            rmdir($path);
        }
    }

    public function testCanGetDocumentById()
    {
        $manager = bootstrap::getServiceManager()->get('dms.manager');
        $m_document = $manager->loadDocument('2b5c466bf06d665b479e85c48ec733d235d13884')->getDocument();

        $this->assertInstanceOf("Dms\Document\Document", $m_document);
        $this->assertNotNull($m_document->getDatas());
        $this->assertNotNull($m_document->getId());
        $this->assertNotNull($m_document->getSupport());
    }

    /**
     * @depends testCanGetDocumentById
     */
    public function testCanGetDocument()
    {
        $manager = bootstrap::getServiceManager()->get('dms.manager');
        $m_document = $manager->getDocument();
        $this->assertInstanceOf("Dms\Document\Document", $m_document);
    }

    /**
     * @depends testCanGetDocument
     */
    public function testCanClearManager()
    {
        $manager = bootstrap::getServiceManager()->get('dms.manager');
        $old_document_id = $manager->getDocument()->getId();
        $manager->clear();
        $m_document = $manager->getDocument();
        $this->assertInstanceOf("Dms\Document\Document", $m_document);
        $this->assertNotEquals($old_document_id, $m_document->getId());
    }

    public function testCanGetInfoDocumentWithoutData()
    {
        $manager = bootstrap::getServiceManager()->get('dms.manager');
        $m_document = $manager->loadDocumentInfo('2b5c466bf06d665b479e85c48ec733d235d13884')->getDocument();

        $this->assertInstanceOf("Dms\Document\Document", $m_document);
        $this->assertNull($m_document->getDatas());
        $this->assertNotNull($m_document->getId());
        $this->assertNotNull($m_document->getSupport());
    }

    public function testCanRecordDocument()
    {
        $image = file_get_contents(__DIR__ . '/../../_file/gnu.png');
        $document['id'] = '0000filname';
        $document['coding'] = 'binary';
        $document['data'] = $image;

        $manager = bootstrap::getServiceManager()->get('dms.manager');

        $document = $manager->createDocument($document)->writeFile()->getDocument();
        $document_id = $document->getId();

        $this->assertEquals(11, strlen($document_id));
        $this->assertFileExists(__DIR__ . '/../../_upload/' . substr($document_id, 0, 2) . '/' . substr($document_id, 2, 2) . '/' . substr($document_id, 4) . '.dat');
        $this->assertFileExists(__DIR__ . '/../../_upload/' . substr($document_id, 0, 2) . '/' . substr($document_id, 2, 2) . '/' . substr($document_id, 4) . '.inf');
        $this->assertEquals($image, file_get_contents(__DIR__ . '/../../_upload/' . substr($document_id, 0, 2) . '/' . substr($document_id, 2, 2) . '/' . substr($document_id, 4) . '.dat'));
    }

    public function testCanDecodeBase()
    {
        $image = file_get_contents(__DIR__ . '/../../_file/gnu.png');
        $document['id'] = '0001filname';
        $document['coding'] = 'base';
        $document['data'] = base64_encode($image);

        $manager = bootstrap::getServiceManager()->get('dms.manager');
        $manager->decode($document);
        $document = $manager->getDocument();
        $this->assertEquals('binary', $document->getEncoding());
        $this->assertEquals($image, $document->getDatas());
    }

    public function testCanResizeDocument()
    {
        $image = file_get_contents(__DIR__ . '/../../_file/gnu.png');
        $document['id'] = '0002filname';
        $document['coding'] = 'binary';
        $document['data'] = $image;

        $manager = bootstrap::getServiceManager()->get('dms.manager');
        $manager->createDocument($document);
        $manager->setSize('80x80');
        $manager->writeFile();
        $document = $manager->getDocument();

        $this->assertTrue(strlen($document->getDatas()) < strlen($image));
    }

    public function testCanFomatDocument()
    {
        $image = file_get_contents(__DIR__ . '/../../_file/gnu.png');
        $document['id'] = '0002filname';
        $document['coding'] = 'binary';
        $document['data'] = $image;
        $document['type'] = 'png';

        $manager = bootstrap::getServiceManager()->get('dms.manager');
        $manager->createDocument($document);
        $manager->setFormat('pdf');
        $manager->writeFile();
        $document = $manager->getDocument();

        $this->assertTrue(strlen($document->getDatas()) < strlen($image));
    }

    public function testCanFomatDocumentOdttoPdf()
    {
        $image = file_get_contents(__DIR__ . '/../../_file/odt.odt');
        $document['id'] = '0002odt';
        $document['coding'] = 'binary';
        $document['data'] = $image;
        $document['type'] = 'odt';

        $manager = bootstrap::getServiceManager()->get('dms.manager');
        $manager->createDocument($document);
        $manager->setFormat('pdf');
        $manager->writeFile();
        $document = $manager->getDocument();

        $this->assertTrue(strlen($document->getDatas()) < strlen($image));
    }

    public function testCanFomatDocumentOdtToBmp()
    {
        $image = file_get_contents(__DIR__ . '/../../_file/odt.odt');
        $document['id'] = '0002odt.bmp';
        $document['coding'] = 'binary';
        $document['data'] = $image;
        $document['type'] = 'odt';

        $manager = bootstrap::getServiceManager()->get('dms.manager');
        $manager->createDocument($document);
        $manager->setFormat('jpg');
        $manager->writeFile();
        $document = $manager->getDocument();

        $this->assertTrue(strlen($document->getDatas()) < strlen($image));
    }
    
    public function testCanFomatDocumentDocxToPdf()
    {
    	$image = file_get_contents(__DIR__ . '/../../_file/docx.docx');
    	$document['id'] = '0002docx';
    	$document['coding'] = 'binary';
    	$document['data'] = $image;
    	$document['type'] = 'docx';
    
    	$manager = bootstrap::getServiceManager()->get('dms.manager');
    	$manager->createDocument($document);
    	$manager->setFormat('pdf');
    	$manager->writeFile();
    	$document = $manager->getDocument();

    	$this->assertTrue(strlen($document->getDatas()) > strlen($image));
    }

    public function testCanGetStorage()
    {
        $dm = bootstrap::getServiceManager()->get('dms.manager');
        $storage = $dm->getStorage();
        $this->assertInstanceOf("Dms\Storage\StorageInterface", $storage);
    }
}
