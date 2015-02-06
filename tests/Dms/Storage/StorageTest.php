<?php

namespace Dms\Storage;

use \PHPUnit_Framework_TestCase;
use Dms\Document\Document;

class StorageTest extends PHPUnit_Framework_TestCase
{
    public function tearDown()
    {
        @unlink(__DIR__ . '/../../_upload/pr/fx/filename.dat');
        @unlink(__DIR__ . '/../../_upload/pr/fx/filename.inf');
        @rmdir(__DIR__ . '/../../_upload/pr/fx');
        @rmdir(__DIR__ . '/../../_upload/pr');
    }

    public function testCanWriteData()
    {
        $path = __DIR__ . '/../../_upload/';
        $data = 'data write binary support data';
        $st = new Storage(array('path' => $path));
        
        $doc = new Document();
        $doc->setId('prfxfilename');
        $doc->setDatas($data);
        
        $res = $st->write($doc);
        $this->assertFileExists($path . 'pr/fx/filename.dat');
        $this->assertTrue(filesize($path . 'pr/fx/filename.dat')===strlen($data));
        $this->assertFileExists($path . 'pr/fx/filename.inf');
        $this->assertEquals(file_get_contents($path . 'pr/fx/filename.dat'),$data);
    }

    public function testCanRead()
    {
        $path = __DIR__ . '/../../_upload';
        $st = new Storage(array('path' => $path));
        
        $doc = new Document();
        $doc->setId('e2bd813816c305a8a22e03c95d2ee8fd3f7bc710');
        
        $file = $st->read($doc, 'fdff');

        $this->assertEquals(file_get_contents(__DIR__ . '/../../_upload/e2/bd/813816c305a8a22e03c95d2ee8fd3f7bc710.inf'),serialize($doc));
    }
}
