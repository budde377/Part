<?php
/**
 * Created by PhpStorm.
 * User: budde
 * Date: 3/3/15
 * Time: 6:39 PM
 */

namespace ChristianBudde\Part\util\file;




use ChristianBudde\Part\controller\ajax\StubTypeHandlerLibraryImpl;

class TypeHandlerGeneratorFileImplTest extends \PHPUnit_Framework_TestCase{

    /** @var  TypeHandlerGeneratorFileImpl */
    private $generator;
    /** @var  FileImpl */
    private $file;

    public function testCopyReturnsRightInstance()
    {

        $this->assertNull($this->file->copy('test'));
    }

    public function testCopyReturnsRightInstanceOnSuccess()
    {
        $file = new TypeHandlerGeneratorFileImpl(new StubTypeHandlerLibraryImpl(), new FileImpl("/tmp/".uniqid()));
        $file->write("test");
        $this->assertTrue($file->exists());
        $file2 = $file->copy("/tmp/".uniqid());
        $this->assertInstanceOf("ChristianBudde\\Part\\util\\file\\TypeHandlerGeneratorFileImpl",  $file2);
    }


    protected function setUp()
    {
        $this->generator = new TypeHandlerGeneratorFileImpl(new StubTypeHandlerLibraryImpl(), $this->file = new FileImpl('nonExistingFile'));
    }


    public function testGenerator()
    {

        $this->assertTrue($this->generator->generateTypeHandler() === $this->file);
    }

    public function testPath()
    {

        $this->assertEquals($this->generator->getAbsoluteFilePath(),$this->file->getAbsoluteFilePath() );
    }




}