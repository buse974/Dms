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

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testCanGetData()
    {
    	$funct = function($content) {
    		$this->assertControllerName('ged_document');
    		$this->assertActionName('get');
    		$this->assertResponseStatusCode(200);
    		$this->assertEquals(147186, strlen($content));
    		
    		return true;
    	};
    	
    	ob_start($funct);
        $this->dispatch('/data/e2bd813816c305a8a22e03c95d2ee8fd3f7bc710','GET');
        ob_flush();
        ob_clean();
    }

    
    public function testCanNotGetData()
    {
        $this->dispatch('/data/e2bd813816c305a8a22e03c95d2ee8fd3f7bc71012','GET');
        
        $this->assertControllerName('ged_document');
        $this->assertActionName('get');
        $this->assertResponseStatusCode(200);

        $this->assertEquals("Param is not id: e2bd813816c305a8a22e03c95d2ee8fd3f7bc71012", $this->getResponse()->getContent());
    }

    public function testCanGetType()
    {
        $this->dispatch('/type/e2bd813816c305a8a22e03c95d2ee8fd3f7bc710','GET');

        $this->assertControllerName('ged_document');
        $this->assertActionName('gettype');
        $this->assertResponseStatusCode(200);
        $this->assertEquals("image/png", $this->getResponse()->getContent());
    }

    public function testCanGetName()
    {
        $this->dispatch('/name/e2bd813816c305a8a22e03c95d2ee8fd3f7bc710','GET');

        $this->assertControllerName('ged_document');
        $this->assertActionName('getname');
        $this->assertResponseStatusCode(200);

        $this->assertEquals("gnu.png", $this->getResponse()->getContent());
    }

    public function testCanGetDescription()
    {
        $this->dispatch('/description/e2bd813816c305a8a22e03c95d2ee8fd3f7bc710','GET');

        $this->assertControllerName('ged_document');
        $this->assertActionName('getdescription');
        $this->assertResponseStatusCode(200);

        $this->assertEquals(null, $this->getResponse()->getContent());
    }

   /* public function testInitSession()
    {
        $this->dispatch('/initsession','GET');

        $this->assertControllerName('ged_document');
        $this->assertActionName('initsession');
        $this->assertResponseStatusCode(200);
        $tab = json_decode($this->getResponse()->getContent(),true);

        $this->assertTrue($tab['result']);
    }*/

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
