<?php

namespace BaseTest\Base\Base;

use \PHPUnit_Framework_TestCase;
use \Dms\Coding\Url\Url;

class UrlTest extends PHPUnit_Framework_TestCase
{
    public function testCanEncode()
    {
    	$in = 'http://url';
    	
    	$coding = new Url();
    	$out_decoded = $coding->encode($in);
    	
    	$this->assertFalse($out_decoded);
    }
    
    public function testCanDecode()
    {
    	$data_adapter = file_get_contents(__DIR__ . '/../../../_file/response.data');
    	$img = file_get_contents(__DIR__ . '/../../../_file/test.png');
    	
    	$mock_adapter = $this->getMockBuilder('\Zend\Http\Client\Adapter\Socket')->disableOriginalConstructor()->getMock();
    	$mock_adapter->expects($this->any())->method('read')->will($this->returnValue($data_adapter));
    	
    	$in = 'http://dms.test';
    	
    	$coding = new Url();
    	$coding->setAdapter($mock_adapter);
    	$out_decoded = $coding->decode($in);
    	
    	$this->assertEquals($img, $out_decoded);
    }
    
    public function testCanGetNameCoding()
    {
    	$coding = new Url();
    	$name = $coding->getCoding();
    
    	$this->assertEquals($name,Url::CODING_URL_STR);
    }
}
