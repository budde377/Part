<?php

/**
 * Created by PhpStorm.
 * User: budde
 * Date: 5/13/14
 * Time: 10:02 PM
 */
class DumpFileImplTest extends PHPUnit_Framework_TestCase
{

    /** @var  DumpFileImpl */
    private $dumpFile;

    public function setUp()
    {
        $this->dumpFile= new DumpFileImpl(dirname(__FILE__) . "/stubs/dumpFile");
        $this->dumpFile->delete();

    }

    /* THIS TEST ASSUMES THAT THE DumpFileImpl EXTENDS FileImpl. */

    public function testDumpFileCanDumpArray() {
        $this->dumpFile->writeSerialized($v = [1,2,3]);
        $this->assertEquals([$v], $this->dumpFile->getUnSerializedContent());
    }

    public function testDumpFileCanDumpMultiple() {
        $this->dumpFile->writeSerialized($v1 = [1,2,3]);
        $this->dumpFile->writeSerialized($v2 = [4,5,6]);
        $this->assertEquals([$v1,$v2], $this->dumpFile->getUnSerializedContent());
    }


    public function tearDown()
    {
        $this->dumpFile->delete();
    }


}