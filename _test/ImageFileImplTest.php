<?php
require_once dirname(__FILE__) . '/../_class/ImageFileImpl.php';
require_once dirname(__FILE__) . '/../_class/FileImpl.php';
/**
 * Created by JetBrains PhpStorm.
 * User: budde
 * Date: 6/3/12
 * Time: 8:46 PM
 * To change this template use File | Settings | File Templates.
 */
class ImageFileImplTest extends PHPUnit_Framework_TestCase
{
    /** @var  ImageFileImpl */
    private $imageFile;
    /** @var  ImageFileImpl */
    private $notImageFile;
    /** @var  ImageFileImpl */
    private $nonExistingFile;

    public function setUp(){
        $file = new ImageFileImpl(dirname(__FILE__).'/_stub/imageFileStub300x200.png');
        $f = $file->copy($file->getParentFolder()->getAbsolutePath().'/imgStub.png');
        $this->imageFile = new ImageFileImpl($f->getAbsoluteFilePath());
        $file = new ImageFileImpl(dirname(__FILE__).'/_stub/fileStub');
        $this->notImageFile = $file->copy(dirname(__FILE__).'/_stub/fileStub2');
        $this->nonExistingFile = new ImageFileImpl('nonExistingFile');
    }


    public function testCopyWillReturnInstanceOfImageFile(){
        $f = $this->imageFile->copy($this->imageFile->getParentFolder()->getAbsolutePath().'/imgStub2.png');
        $f->delete();
        $this->assertInstanceOf('ImageFileImpl', $f);
    }

    public function testGetWidthHeightRatioOfNonExistingFileReturnsNull(){
        $this->assertNull($this->nonExistingFile->getWidth());
        $this->assertNull($this->nonExistingFile->getHeight());
        $this->assertNull($this->nonExistingFile->getRatio());
    }

    public function testGetWidthHeightRatioOfNonImageFileReturnsNull(){
        $this->assertNull($this->notImageFile->getHeight());
        $this->assertNull($this->notImageFile->getWidth());
        $this->assertNull($this->notImageFile->getRatio());
    }

    public function testGeDimensionsOfImageReturnsRightDimensions(){
        $this->assertEquals(300, $this->imageFile->getWidth());
        $this->assertEquals(200, $this->imageFile->getHeight());
        $this->assertEquals(300/200, $this->imageFile->getRatio());
    }

    public function testForceSizeWillForceSize(){
        $this->imageFile->forceSize(100,100);
        $this->assertEquals(100, $this->imageFile->getWidth());
        $this->assertEquals(100,$this->imageFile->getHeight());
        $this->assertEquals(1, $this->imageFile->getRatio());
    }

    public function testScaleWidthChangeWidthAndPreserveRatio(){
        $this->imageFile->scaleToWidth(150);
        $this->assertEquals(150, $this->imageFile->getWidth());
        $this->assertEquals(300/200, $this->imageFile->getRatio());
    }

    public function testScaleHeightChangeHeightAndPreserveRatio(){
        $this->imageFile->scaleToHeight(100);
        $this->assertEquals(100, $this->imageFile->getHeight());
        $this->assertEquals(300/200, $this->imageFile->getRatio());
    }

    public function testScaleToInnerBoxWillScaleToInnerBox(){
        $this->imageFile->scaleToInnerBox(100,100);
        $this->assertEquals(150, $this->imageFile->getWidth());
        $this->assertEquals(100, $this->imageFile->getHeight());
    }

    public function testScaleToInnerBoxLargerThanImageIsScaling(){
        $this->imageFile->scaleToInnerBox(300,300);
        $this->assertEquals(450, $this->imageFile->getWidth());
        $this->assertEquals(300, $this->imageFile->getHeight());
    }

    public function testScaleToOuterBoxWillScaleToOuterBox(){
        $this->imageFile->scaleToOuterBox(150,150);
        $this->assertEquals(150, $this->imageFile->getWidth());
        $this->assertEquals(100, $this->imageFile->getHeight());
    }

    public function testScaleToOuterBoxWillScaleToOuterBoxWhenLargerThanImage(){
        $this->imageFile->scaleToOuterBox(600,600);
        $this->assertEquals(600, $this->imageFile->getWidth());
        $this->assertEquals(400, $this->imageFile->getHeight());
    }

    public function testCropWillCropImage(){
        $this->imageFile->crop(40,40,100,100);
        $this->assertEquals(100, $this->imageFile->getWidth());
        $this->assertEquals(100, $this->imageFile->getHeight());
    }

    public function testCropWillNotIncludeAreaOutsideOfImage(){
        $this->imageFile->crop(200,100,200,200);
        $this->assertEquals(100, $this->imageFile->getWidth());
        $this->assertEquals(100, $this->imageFile->getHeight());
    }


    public function tearDown(){
        $this->imageFile->delete();
        $this->notImageFile->delete();
    }

}
