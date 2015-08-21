<?php

namespace Dms\Resize;

use \PHPUnit_Framework_TestCase;

class ResizeTest extends PHPUnit_Framework_TestCase
{
    public function testCangetResizeData()
    {
    	$file = file_get_contents(__DIR__ . '/../../_file/gnu.png');
    	$file_resize = file_get_contents(__DIR__ . '/../../_file/gnu80x80.png');
    	
    	$resize = new Resize();
    	$resize->setData($file);
    	$resize->setFormat('png');
    	
    	$this->assertEquals($resize->getResizeData('80x80'), $file_resize);
    	$this->assertEquals($resize->getFormat(), 'png');
    	$this->assertEquals($resize->getTypeMine(), 'image/png');
    }
}
