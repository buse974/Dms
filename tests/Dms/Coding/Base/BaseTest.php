<?php

namespace BaseTest\Base\Base;

use \PHPUnit_Framework_TestCase;
use Dms\Coding\Base\Base;

class BaseTest extends PHPUnit_Framework_TestCase
{
    public function testCanEncode()
    {
    	$data = 'stirng test';
    	$data_base = base64_encode($data);
    	
        $codingbase = new Base();
        $out_base = $codingbase->encode($data);
        
        $this->assertEquals($data_base,$out_base);
    }
    
    public function testCanDecode()
    {
    	$data = 'stirng test';
    	$data_base = base64_encode($data);
    	 
    	$codingbase = new Base();
    	$out = $codingbase->decode($data_base);
    
    	$this->assertEquals($data,$out);
    }
    
    public function testCanDecodeWhithStringJsEncoding()
    {
    	$data = 'stirng test';
    	$data_base = 'base64,' . base64_encode($data);
    
    	$codingbase = new Base();
    	$out = $codingbase->decode($data_base);
    
    	$this->assertEquals($data,$out);
    }
    
    public function testCanGetNameCoding()
    {
    	$codingbase = new Base();
    	$name = $codingbase->getCoding();
    
    	$this->assertEquals($name,Base::CODING_BASE_STR);
    }
}
