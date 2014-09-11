<?php
/**
 * Created by PhpStorm.
 * User: budde
 * Date: 5/17/14
 * Time: 5:00 PM
 */
use ChristianBudde\cbweb\StubDumpFileImpl;

class StubDumpFileImplTest extends PHPUnit_Framework_TestCase{
    /** @var  StubDumpFileImpl */
    private $dumpFile;

    protected function setUp(){
        $this->dumpFile = new StubDumpFileImpl();
    }
    public function testCreateDoesNotExists(){
        $this->assertFalse($this->dumpFile->exists());

    }


    public function testCreateDoesNothing(){
        $this->dumpFile->create();
        $this->assertFalse($this->dumpFile->exists());

    }

    public function testDumpVarDoesNothing(){
        $this->dumpFile->dumpVar("name", $this);
        $this->assertFalse($this->dumpFile->exists());
    }
} 