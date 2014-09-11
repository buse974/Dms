<?php

namespace Dms\Storage;

use \PHPUnit_Framework_TestCase;

class StorageTest extends PHPUnit_Framework_TestCase
{
    public function tearDown()
    {
        @unlink(__DIR__ . '/../../_upload/pr/fx/filename');
        @rmdir(__DIR__ . '/../../_upload/pr/fx');
        @rmdir(__DIR__ . '/../../_upload/pr');
    }

    public function testCanWriteData()
    {
        $path = __DIR__ . '/../../_upload/';
        $data = 'data write binary support data';
        $st = new Storage(array('path' => $path));
        $res = $st->write($data, 'prfxfilename');

        $this->assertTrue($res===strlen($data));
        $this->assertFileExists($path . 'pr/fx/filename');
        $this->assertEquals(file_get_contents($path . 'pr/fx/filename'),$data);
    }

    public function testCanRead()
    {
        $path = __DIR__ . '/../../_upload';
        $st = new Storage(array('path' => $path));
        $file = $st->read('2b5c466bf06d665b479e85c48ec733d235d13884.inf');

        $this->assertEquals(file_get_contents(__DIR__ . '/../../_upload/2b/5c/466bf06d665b479e85c48ec733d235d13884.inf'),$file);
    }
}
