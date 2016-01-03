<?php
/**
 * Created by PhpStorm.
 * User: budde
 * Date: 3/3/15
 * Time: 6:43 PM
 */

namespace ChristianBudde\Part\util\file;



use ChristianBudde\Part\controller\ajax\StubTypeHandlerLibraryImpl;

class TypeHandlerGeneratorImageFileImplTest extends \PHPUnit_Framework_TestCase{
    /** @var  TypeHandlerGeneratorImageFileImpl */
    private $generator;
    /** @var  File */
    private $file;

    protected function setUp()
    {
        $this->generator = new TypeHandlerGeneratorImageFileImpl(new StubTypeHandlerLibraryImpl(), $this->file = new ImageFileImpl('nonExistingFile'));
    }


    public function testCopyReturnsRightInstance()
    {

        $this->assertNull($this->file->copy('test'));
    }

    public function testCopyReturnsRightInstanceOnSuccess()
    {
        $file = new TypeHandlerGeneratorImageFileImpl(new StubTypeHandlerLibraryImpl(), new ImageFileImpl("/tmp/".uniqid()));
        $file->write("test");
        $this->assertTrue($file->exists());
        $file2 = $file->copy("/tmp/".uniqid());
        $this->assertInstanceOf("ChristianBudde\\Part\\util\\file\\TypeHandlerGeneratorImageFileImpl",  $file2);
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