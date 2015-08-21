<?php

namespace Dms\FFmpeg;

use \PHPUnit_Framework_TestCase;

class FFmpegTest extends PHPUnit_Framework_TestCase
{
    public function testgetPicture()
    {
        $ff = new FFmpeg;
        $ff->setFile(__DIR__ . '/../../_file/bunny.mp4');
        $file = file_get_contents(__DIR__ . '/../../_file/picture.jpg');
      
        $this->assertEquals($ff->getPicture(), $file);
        $this->assertEquals($ff->getFormat(), 'jpg');
        $this->assertEquals($ff->getTypeMine(), 'image/jpeg');
    }
}
