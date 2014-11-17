<?php

namespace DmsTest\Document;

use \PHPUnit_Framework_TestCase;
use Dms\Document\MimeType;

class MimeTypeTest extends PHPUnit_Framework_TestCase
{
	public function testCanGetExtensionByMimeType()
	{
		$mime = 'image/png';
		$ext = 'png';
		$ext_out = MimeType::getExtensionByMimeType($mime);
		
		$this->assertEquals($ext_out, $ext);	
	}
	
	public function testCanGetMimeTypeByExtension()
	{
		$mime = 'image/png';
		$ext = 'png';
		$mime_out = MimeType::getMimeTypeByExtension($ext);
	
		$this->assertEquals($mime_out, $mime);
	}
}
