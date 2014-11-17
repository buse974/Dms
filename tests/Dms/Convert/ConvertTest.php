<?php

namespace DmsTest\Convert;

use \PHPUnit_Framework_TestCase;
use Dms\Convert\Convert;
use Dms\Convert\Process;

class ConvertTest extends PHPUnit_Framework_TestCase
{
    public function testCanGetConvertData()
    {
    	$data_out = "datas";
    	$mock_process = $this->getMockBuilder('\Dms\Convert\Process')->disableOriginalConstructor()->getMock();
    	$mock_process->expects($this->any())->method('setCmd')->will($this->returnValue($mock_process));
    	$mock_process->expects($this->any())->method('run')->will($this->returnValue($data_out));
    	
    	$convert = new Convert();
    	$convert->setProcess($mock_process);
    	$convert->setData('fdzefsd');
    	$convert->setTmp('.');
    	$out = $convert->getConvertData('pdf');
    	
    	$this->assertEquals($data_out, $out);
    }
}
