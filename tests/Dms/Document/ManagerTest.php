<?php

namespace DmsTest\Document;

use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;

class ManagerTest extends AbstractHttpControllerTestCase
{
    public function setUp()
    {
        $this->setApplicationConfig(include __DIR__ . '/../../config/application.config.php');
        parent::setUp();
    }
    
    public function tearDown()
    {
        $this->deleteDirRec(__DIR__ . '/../../_upload/00');
        $this->deleteDirRec(__DIR__ . '/../../_upload/02');
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
        $manager = $this->getApplicationServiceLocator()->get('dms.manager');
        $m_document = $manager->loadDocument('e2bd813816c305a8a22e03c95d2ee8fd3f7bc710')->getDocument();

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
        $manager = $this->getApplicationServiceLocator()->get('dms.manager');
        $m_document = $manager->getDocument();
        $this->assertInstanceOf("Dms\Document\Document", $m_document);
    }

    /**
     * @depends testCanGetDocument
     */
    public function testCanClearManager()
    {
        $manager = $this->getApplicationServiceLocator()->get('dms.manager');
        $old_document_id = $manager->getDocument()->getId();
        $manager->clear();
        $m_document = $manager->getDocument();
        $this->assertInstanceOf("Dms\Document\Document", $m_document);
        $this->assertNotEquals($old_document_id, $m_document->getId());
    }

    public function testCanRecordDocument()
    {
        $image = file_get_contents(__DIR__ . '/../../_file/gnu.png');
        $document['id'] = '0000filname';
        $document['coding'] = 'binary';
        $document['data'] = $image;

        $manager = $this->getApplicationServiceLocator()->get('dms.manager');

        $document = $manager->createDocument($document)->writeFile()->getDocument();
        $document_id = $document->getId();

        $this->assertEquals(11, strlen($document_id));
        $this->assertFileExists(__DIR__ . '/../../upload/' . substr($document_id, 0, 2) . '/' . substr($document_id, 2, 2) . '/' . substr($document_id, 4) . '.dat');
        $this->assertFileExists(__DIR__ . '/../../upload/' . substr($document_id, 0, 2) . '/' . substr($document_id, 2, 2) . '/' . substr($document_id, 4) . '.inf');
        $this->assertEquals($image, file_get_contents(__DIR__ . '/../../upload/' . substr($document_id, 0, 2) . '/' . substr($document_id, 2, 2) . '/' . substr($document_id, 4) . '.dat'));
    }

    public function testCanDecodeBase()
    {
        $image = file_get_contents(__DIR__ . '/../../_file/gnu.png');
        $document['id'] = '0001filname';
        $document['coding'] = 'base';
        $document['data'] = base64_encode($image);

        $manager = $this->getApplicationServiceLocator()->get('dms.manager');
        $manager->createDocument($document);
        $manager->decode();
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

        $manager = $this->getApplicationServiceLocator()->get('dms.manager');
        $manager->createDocument($document);
        $manager->setSize('80x80');
        $manager->writeFile('0002filname.png');
        $document = $manager->getDocument();

        $this->assertTrue(strlen($document->getDatas()) < strlen($image));
    }
    
    public function testCanGetPictureDocument()
    {
        $image = file_get_contents(__DIR__ . '/../../_file/video.jpg');
        
        $manager = $this->getApplicationServiceLocator()->get('dms.manager');
        
        $manager->loadDocument('2222video');
        $manager->setFormat('jpg');
        $manager->writeFile('2222video.jpg');
        $document = $manager->getDocument();

        $this->assertTrue(strlen($document->getDatas()) === strlen($image));
        $this->assertEquals($document->getId(),'2222video.jpg');
    }

    public function testCanFomatDocument()
    {
        $image = file_get_contents(__DIR__ . '/../../_file/gnu.png');
        $document['id'] = '0002filname';
        $document['coding'] = 'binary';
        $document['data'] = $image;
        $document['format'] = 'png';

        $manager = $this->getApplicationServiceLocator()->get('dms.manager');
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
        $document['format'] = 'odt';

        $manager = $this->getApplicationServiceLocator()->get('dms.manager');
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
        $document['format'] = 'odt';

        $manager = $this->getApplicationServiceLocator()->get('dms.manager');
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
        $document['format'] = 'docx';

        $manager = $this->getApplicationServiceLocator()->get('dms.manager');
        $manager->createDocument($document);
        $manager->setFormat('pdf');
        $manager->writeFile();

        $document = $manager->getDocument();
        $this->assertTrue(strlen($document->getDatas()) > strlen($image));
    }

    public function testCanGetStorage()
    {
        $dm = $this->getApplicationServiceLocator()->get('dms.manager');
        $storage = $dm->getStorage();
        $this->assertInstanceOf("Dms\Storage\StorageInterface", $storage);
    }
}
