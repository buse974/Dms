<?php

namespace DmsTest\Service;

use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;

class DmsServiceTest extends AbstractHttpControllerTestCase
{
    static public $container;
    
    public function setUp()
    {
        $this->setApplicationConfig(include __DIR__ . '/../../config/application.config.php');
        parent::setUp();
    }
    
    public function tearDown()
    {
        $this->deleteDirRec(__DIR__ . '/../../_upload/01');
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

    public function testAddDocumentBases()
    {
        $image = file_get_contents(__DIR__ . '/../../_file/gnu.png');

        $document['id'] = '0200filename';
        $document['coding'] = 'base';
        $document['type'] = 'image/png';
        $document['data'] =  'data:image/jpeg;base64,' . base64_encode($image);

        self::$container = $this->getApplicationServiceLocator();
        
        $dms = self::$container->get('dms.service');
        $ret = $dms->add($document);

        $this->assertEquals(12, strlen($ret));
        $this->assertFileExists(__DIR__ . '/../../upload/' . substr($ret, 0, 2) . '/' . substr($ret, 2, 2) . '/' . substr($ret, 4) . '.dat');
        $this->assertEquals($image, file_get_contents(__DIR__ . '/../../upload/' . substr($ret, 0, 2) . '/' . substr($ret, 2, 2) . '/' . substr($ret, 4) . '.dat'));
    }

    /**
     *
     * @depends testAddDocumentBases
     */
    public function testResize()
    {
        $dms = self::$container->get('dms.service');
        $ret = $dms->resize('80x80');

        $this->assertEquals(strlen('0200filename') + strlen('-80x80') , strlen($ret));
        $this->assertFileExists(__DIR__ . '/../../upload/' . substr($ret, 0, 2) . '/' . substr($ret, 2, 2) . '/' . substr($ret, 4) . '.dat');
    }

    public function testAddDocumentOdt()
    {
        $image = file_get_contents(__DIR__ . '/../../_file/odt.odt');
        $document['id'] = '0200filenameodt';
        $document['coding'] = 'base';
        $document['data'] = base64_encode($image);
        $document['type'] = 'odt';

        $dms = self::$container->get('dms.service');
        $ret = $dms->add($document);

        $this->assertEquals(15, strlen($ret));
        $this->assertFileExists(__DIR__ . '/../../upload/' . substr($ret, 0, 2) . '/' . substr($ret, 2, 2) . '/' . substr($ret, 4) . '.dat');
        $this->assertEquals($image, file_get_contents(__DIR__ . '/../../upload/' . substr($ret, 0, 2) . '/' . substr($ret, 2, 2) . '/' . substr($ret, 4) . '.dat'));
    }
}
