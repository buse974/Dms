<?php

namespace BaseTest\Base\Base;

use \PHPUnit_Framework_TestCase;
use Dms\Coding\Deflate\Deflate;

class DefalteTest extends PHPUnit_Framework_TestCase
{
    public function testCanEncode()
    {
    	$in = 'stirng test';
    	$in_encoded = gzdeflate($in);
    	
        $coding = new Deflate();
        $out_encoded = $coding->encode($in);
        
        $this->assertEquals($in_encoded,$out_encoded);
    }
    
    public function testCanDecode()
    {
    	$in = 'stirng test';
    	$in_encoded = gzdeflate($in);
    	 
    	$coding = new Deflate();
    	$out_decoded = $coding->decode($in_encoded);
    
    	$this->assertEquals($in,$out_decoded);
    }
    
    public function testCanGetNameCoding()
    {
    	$coding = new Deflate();
    	$name = $coding->getCoding();
    
    	$this->assertEquals($name,Deflate::CODING_DEFLATE_STR);
    }
}
