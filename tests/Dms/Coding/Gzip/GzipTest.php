<?php

namespace BaseTest\Base\Base;

use \PHPUnit_Framework_TestCase;
use Dms\Coding\Gzip\Gzip;

class GzipTest extends PHPUnit_Framework_TestCase
{
    public function testCanEncode()
    {
    	$in = 'stirng test';
    	$in_encoded = gzencode($in);
    	
        $coding = new Gzip();
        $out_encoded = $coding->encode($in);
        
        $this->assertEquals($in_encoded,$out_encoded);
    }
    
    public function testCanDecode()
    {
    	$in = 'stirng test';
    	$in_encoded = gzencode($in);
    	 
    	$coding = new Gzip();
    	$out_decoded = $coding->decode($in_encoded);
    
    	$this->assertEquals($in,$out_decoded);
    }
    
    public function testCanGetNameCoding()
    {
    	$coding = new Gzip();
    	$name = $coding->getCoding();
    
    	$this->assertEquals($name,Gzip::CODING_GZIP_STR);
    }
}
