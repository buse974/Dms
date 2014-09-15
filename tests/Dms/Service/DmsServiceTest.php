<?php

namespace DmsTest\Service;

use \PHPUnit_Framework_TestCase;
use DmsTest\bootstrap;

class DmsServiceTest extends PHPUnit_Framework_TestCase
{
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
        $document['data'] = base64_encode($image);

        $dms = bootstrap::getServiceManager()->get('dms.service');
        $ret = $dms->add($document);

        $this->assertEquals(12, strlen($ret));
        $this->assertFileExists(__DIR__ . '/../../_upload/' . substr($ret, 0, 2) . '/' . substr($ret, 2, 2) . '/' . substr($ret, 4) . '.dat');
        $this->assertEquals($image, file_get_contents(__DIR__ . '/../../_upload/' . substr($ret, 0, 2) . '/' . substr($ret, 2, 2) . '/' . substr($ret, 4) . '.dat'));
    }

    /**
     *
     * @depends testAddDocumentBases
     */
    public function testResize()
    {
        $dms = bootstrap::getServiceManager()->get('dms.service');
        $ret = $dms->resize('80x80');

        $this->assertEquals(12 + strlen('-80x80.jpeg') , strlen($ret));
        $this->assertFileExists(__DIR__ . '/../../_upload/' . substr($ret, 0, 2) . '/' . substr($ret, 2, 2) . '/' . substr($ret, 4) . '.dat');
    }

    public function testAddDocumentOdt()
    {
        $image = file_get_contents(__DIR__ . '/../../_file/odt.odt');
        $document['id'] = '0200filenameodt';
        $document['coding'] = 'base';
        $document['data'] = base64_encode($image);
        $document['type'] = 'odt';

        $dms = bootstrap::getServiceManager()->get('dms.service');
        $ret = $dms->add($document);

        $this->assertEquals(15, strlen($ret));
        $this->assertFileExists(__DIR__ . '/../../_upload/' . substr($ret, 0, 2) . '/' . substr($ret, 2, 2) . '/' . substr($ret, 4) . '.dat');
        $this->assertEquals($image, file_get_contents(__DIR__ . '/../../_upload/' . substr($ret, 0, 2) . '/' . substr($ret, 2, 2) . '/' . substr($ret, 4) . '.dat'));
    }
}
