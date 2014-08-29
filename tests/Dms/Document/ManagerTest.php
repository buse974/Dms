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
		if(is_dir($path)){
			rmdir($path);
		}
	}
	
    public function testCanGetDocumentById()
    { 
    	$dm = bootstrap::getServiceManager()->get('dms.manager');
    	$doc = $dm->getDocumentById('2b5c466bf06d665b479e85c48ec733d235d13884');
    	
    	$this->assertInstanceOf("Dms\Document\Document", $doc);
    	$this->assertNotNull($doc->getDatas());
    	$this->assertNotNull($doc->getId());
    	$this->assertNotNull($doc->getSupport());
    }

    /**
     * @depends testCanGetDocumentById
     */
    public function testCanGetDocument()
    {
    	$dm = bootstrap::getServiceManager()->get('dms.manager');
    	$doc = $dm->getDocument();
    	$this->assertInstanceOf("Dms\Document\Document", $doc);
    }
    
    /**
     * @depends testCanGetDocument
     */
    public function testCanClearManager()
    {
    	$dm = bootstrap::getServiceManager()->get('dms.manager');
    	$dm->clear();
    	$doc = $dm->getDocument();
    	$this->assertNull($doc);
    }

    public function testCanGetInfoDocumentWithoutData()
    {  
    	$dm = bootstrap::getServiceManager()->get('dms.manager');
    	$doc = $dm->getInfoDocument('2b5c466bf06d665b479e85c48ec733d235d13884');
    	 
    	$this->assertInstanceOf("Dms\Document\Document", $doc);
    	$this->assertNull($doc->getDatas());
    	$this->assertNotNull($doc->getId());
    	$this->assertNotNull($doc->getSupport());
    }

    public function testCanSetDocumentById()
    {
    	$dm = bootstrap::getServiceManager()->get('dms.manager');
    	$dm->clear();
    	$doc = $dm->setDocument('2b5c466bf06d665b479e85c48ec733d235d13884');
    	$this->assertInstanceOf("Dms\Document\Document", $doc);
    	$this->assertEquals('2b5c466bf06d665b479e85c48ec733d235d13884',$doc->getId());
    }
    
    /**
     * @depends testCanSetDocumentById
     */
    public function testInitDataWithoutDocument()
    {
    	$dm = bootstrap::getServiceManager()->get('dms.manager');
    	$doc = $dm->getDocument();
    	$this->assertNull($doc->getDatas());
    	
    	$ret = $dm->initData();
    	$this->assertTrue($ret);
    	
    	$doc = $dm->getDocument();
    	$this->assertNotNull($doc->getDatas());
    }

    public function testCanRecordDocument()
    {
    	$image = file_get_contents(__DIR__ . '/../../_file/gnu.png');
    	$document['id'] = '0000filname';
    	$document['coding'] = 'binary';
    	$document['data'] = $image;
    	
    	$dm = bootstrap::getServiceManager()->get('dms.manager');
    	$doc = $dm->recordDocument($document);
    	
    	$ret = $doc->getId();
    	$this->assertEquals(11, strlen($doc->getId()));
    	$this->assertFileExists(__DIR__ . '/../../_upload/' . substr($ret, 0, 2) . '/' . substr($ret, 2, 2) . '/' . substr($ret, 4) . '.dat');
    	$this->assertFileExists(__DIR__ . '/../../_upload/' . substr($ret, 0, 2) . '/' . substr($ret, 2, 2) . '/' . substr($ret, 4) . '.inf');
    	$this->assertEquals($image, file_get_contents(__DIR__ . '/../../_upload/' . substr($ret, 0, 2) . '/' . substr($ret, 2, 2) . '/' . substr($ret, 4) . '.dat'));
    }

    public function testCanDecodeBase()
    {
    	$image = file_get_contents(__DIR__ . '/../../_file/gnu.png');
    	$document['id'] = '0001filname';
    	$document['coding'] = 'base';
    	$document['data'] = base64_encode($image);
    	 
    	$dm = bootstrap::getServiceManager()->get('dms.manager');
    	$doc = $dm->decode($document);
    	$this->assertEquals('binary', $doc->getEncoding());
    	$this->assertEquals($image, $doc->getDatas());
    }

    public function testCanResizeDocument()
    {
    	$image = file_get_contents(__DIR__ . '/../../_file/gnu.png');
    	$document['id'] = '0002filname';
    	$document['coding'] = 'binary';
    	$document['data'] = $image;
    	
    	$dm = bootstrap::getServiceManager()->get('dms.manager');
    	$doc = $dm->resizeDocument('80x80',$document);
    	$this->assertTrue(strlen($doc->getDatas()) < strlen($image));
    }

    public function testCanGetStorage()
    {
    	$dm = bootstrap::getServiceManager()->get('dms.manager');
    	$storage = $dm->getStorage();
    	$this->assertInstanceOf("Dms\Storage\StorageInterface", $storage);
    }
}