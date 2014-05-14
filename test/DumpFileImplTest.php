<?php

/**
 * Created by PhpStorm.
 * User: budde
 * Date: 5/13/14
 * Time: 10:02 PM
 */
class DumpFileImplTest extends PHPUnit_Framework_TestCase
{

    /** @var  DumpFile */
    private $dumpFile;

    public function setUp()
    {
        $this->dumpFile= new DumpFileImpl(dirname(__FILE__) . "/stubs/dumpFile");
        $this->dumpFile->delete();

    }

    /* THIS TEST ASSUMES THAT THE DumpFileImpl EXTENDS FileImpl. */


    public function testDumpFileDoesWriteDumpOfSimple(){
        $name = "LOL";
        $var = 1;

        $this->dumpFile->dumpVar($name, $var);

        $c = $this->dumpFile->getContents();

        $this->assertContains($name, $c);
        $this->assertContains(print_r($var, true), $c);
    }

    public function testDumpFileDoesWriteDumpOfObject(){
        $name = "LOL";
        $var = $this;

        $this->dumpFile->dumpVar($name, $var);

        $c = $this->dumpFile->getContents();

        $this->assertContains($name, $c);
        $this->assertContains(print_r($var, true), $c);
    }

    public function testDumpFileDoesWriteDumpOfArray(){
        $name = "LOL";
        $var = array(uniqid()=>uniqid(), uniqid() => uniqid());

        $this->dumpFile->dumpVar($name, $var);

        $c = $this->dumpFile->getContents();

        $this->assertContains($name, $c);
        $this->assertContains(print_r($var, true), $c);
    }


    public function testDumpDoesLogMultipleWithSameName(){
        $name = "LOL";
        $var1 = array(uniqid()=>uniqid(), uniqid() => uniqid());
        $var2 = array(uniqid()=>uniqid(), uniqid() => uniqid(), uniqid()=>uniqid());

        $this->dumpFile->dumpVar($name, $var1);
        $this->dumpFile->dumpVar($name, $var2);

        $c = $this->dumpFile->getContents();

        $this->assertContains($name, $c);
        $this->assertContains(print_r($var1, true), $c);
        $this->assertContains(print_r($var2, true), $c);

    }

    public function testDumpDoesLogMultipleWithDifferentName(){
        $name1 = "LOL";
        $var1 = array(uniqid()=>uniqid(), uniqid() => uniqid());
        $name2 = "LOL2";
        $var2 = array(uniqid()=>uniqid(), uniqid() => uniqid(), uniqid()=>uniqid());

        $this->dumpFile->dumpVar($name1, $var1);
        $this->dumpFile->dumpVar($name2, $var2);

        $c = $this->dumpFile->getContents();

        $this->assertContains($name1, $c);
        $this->assertContains($name2, $c);
        $this->assertContains(print_r($var1, true), $c);
        $this->assertContains(print_r($var2, true), $c);

    }


    public function testDumpFileCreateWillWriteToFile(){
        $this->assertEquals(0, strlen($this->dumpFile->getContents()));
        $this->dumpFile->create();
        $this->assertGreaterThan(0, strlen($this->dumpFile->getContents()));
    }

    public function testCreateWillDoNothingIfFileNotEmpty(){
        $this->dumpFile->create();
        $len = strlen($this->dumpFile->getContents());
        $this->dumpFile->create();
        $len2 = strlen($this->dumpFile->getContents());
        $this->assertEquals($len, $len2);
    }

    public function tearDown()
    {
        $this->dumpFile->delete();
    }


}