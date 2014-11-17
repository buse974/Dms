<?php

namespace DmsTest\Convert;

use \PHPUnit_Framework_TestCase;
use \Dms\Convert\Process;
use Dms\Convert\Exception\ProcessException;

class ProcessTest extends PHPUnit_Framework_TestCase
{
	public function testCanRunProcess()
	{
		$process = new Process();
		
		$process->setCmd("echo -n \"test ok\"");
		
		$this->assertEquals($process->run(), 'test ok');
		$this->assertEquals($process->getErrorCode(), 0);
		$this->assertEmpty($process->getErrorMessage());
		$this->assertEquals($process->getOutput(), 'test ok');
	}
	
	public function testCanRunProcessWithInput()
	{
		$process = new Process();
	
		$process->setCmd("cat - ");
		$process->setInput('test ok');
	
		$this->assertEquals($process->run(), 'test ok');
		$this->assertEquals($process->getErrorCode(), 0);
		$this->assertEmpty($process->getErrorMessage());
		$this->assertEquals($process->getOutput(), 'test ok');
	}
	
	public function testCanRunProcessException()
	{
		$this->setExpectedException('Dms\Convert\Exception\ProcessException',null, 127);
		
		$process = new Process();
		$process->setCmd("kgk");
		$process->run();
	}
	
	public function testCanRunProcessError()
	{
		$process = new Process();
		$process->setCmd("notcmd");
		
		try {
			$process->run();
		}
		catch (ProcessException $e) {
			$this->assertEquals($process->getErrorCode(), 127);
			$this->assertContains("notcmd", $process->getErrorMessage());
			$this->assertEmpty($process->getOutput());
			
			return;
		}
		
		$this->fail('An expected exception has not been raised.');
	}
}
