<?php

namespace Dms\Controller;

use Dms\Model\Dms;
use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;

class DocumentControllerTest extends AbstractHttpControllerTestCase
{
    protected $traceError = true;

    public function setUp()
    {
        $this->setApplicationConfig(include __DIR__ . '/../../config/application.config.php');
        parent::setUp();
    }

    public function testCanGetData()
    {
        $this->dispatch('/data/2b5c466bf06d665b479e85c48ec733d235d13884','GET');

        $this->assertControllerName('ged_document');
        $this->assertActionName('get');
        $this->assertResponseStatusCode(200);

        $this->assertEquals(147186, strlen($this->getResponse()->getContent()));
    }

    public function testCanNotGetData()
    {
        $this->dispatch('/data/2b5c466bf06d665b479e85c48ec733d235d138null','GET');

        $this->assertControllerName('ged_document');
        $this->assertActionName('get');
        $this->assertResponseStatusCode(200);

        $this->assertEquals("file 2b5c466bf06d665b479e85c48ec733d235d138null not found", $this->getResponse()->getContent());
    }

    public function testCanGetType()
    {
        $this->dispatch('/type/2b5c466bf06d665b479e85c48ec733d235d13884','GET');

        $this->assertControllerName('ged_document');
        $this->assertActionName('gettype');
        $this->assertResponseStatusCode(200);
        $this->assertEquals("png", $this->getResponse()->getContent());
    }

    public function testCanGetName()
    {
        $this->dispatch('/name/2b5c466bf06d665b479e85c48ec733d235d13884','GET');

        $this->assertControllerName('ged_document');
        $this->assertActionName('getname');
        $this->assertResponseStatusCode(200);

        $this->assertEquals("gnu.png", $this->getResponse()->getContent());
    }

    public function testCanGetDescription()
    {
        $this->dispatch('/description/2b5c466bf06d665b479e85c48ec733d235d13884','GET');

        $this->assertControllerName('ged_document');
        $this->assertActionName('getdescription');
        $this->assertResponseStatusCode(200);

        $this->assertEquals(null, $this->getResponse()->getContent());
    }

    public function testInitSession()
    {
        $this->dispatch('/initsession','GET');

        $this->assertControllerName('ged_document');
        $this->assertActionName('initsession');
        $this->assertResponseStatusCode(200);
        $tab = json_decode($this->getResponse()->getContent(),true);

        $this->assertTrue($tab['result']);
    }

    public function testSaveUploadFile()
    {
    }

    /**
     * @todo
     */
    public function progressAction()
    {
    }

}
