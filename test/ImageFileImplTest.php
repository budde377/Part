<?php
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
        $file = new ImageFileImpl(dirname(__FILE__).'/stubs/imageFileStub300x200.png');
        $f = $file->copy($file->getParentFolder()->getAbsolutePath().'/imgStub.png');
        $this->imageFile = new ImageFileImpl($f->getAbsoluteFilePath());
        $file = new ImageFileImpl(dirname(__FILE__).'/stubs/fileStub');
        $this->notImageFile = $file->copy(dirname(__FILE__).'/stubs/fileStub2');
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

    public function testScaleToInnerBoxWillScaleWith0Argument(){
        $this->imageFile->scaleToInnerBox(0,100);
        $this->assertEquals(150, $this->imageFile->getWidth());
        $this->assertEquals(100, $this->imageFile->getHeight());
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

    public function testScaleToOuterBoxWillScaleWith0Argument(){
        $this->imageFile->scaleToOuterBox(150,0);
        $this->assertEquals(150, $this->imageFile->getWidth());
        $this->assertEquals(100, $this->imageFile->getHeight());
    }

    public function testScaleToOuterBoxWillScaleWith0WidthArgument(){
        $this->imageFile->scaleToOuterBox(0,100);
        $this->assertEquals(150, $this->imageFile->getWidth());
        $this->assertEquals(100, $this->imageFile->getHeight());
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

    public function testLimitToInnerBoxWillLimitOnLargeImage(){
        $this->imageFile->limitToInnerBox(100,100);
        $this->assertEquals(150, $this->imageFile->getWidth());
        $this->assertEquals(100, $this->imageFile->getHeight());
    }

    public function testLimitToInnerBoxWillLimitOnHalfLargeImage(){
        $this->imageFile->limitToInnerBox(100,1000);
        $this->assertEquals(300, $this->imageFile->getWidth());
        $this->assertEquals(200, $this->imageFile->getHeight());
    }

    public function testLimitToInnerBoxWillNotScaleOnSmallImage(){
        $this->imageFile->limitToInnerBox(1000,1000);
        $this->assertEquals(300, $this->imageFile->getWidth());
        $this->assertEquals(200, $this->imageFile->getHeight());

    }

    public function testLimitToInnerBoxWith0ArgumentWillAuto(){
        $this->imageFile->limitToInnerBox(150,0);
        $this->assertEquals(150, $this->imageFile->getWidth());
        $this->assertEquals(100, $this->imageFile->getHeight());

    }



    public function testLimitToOuterBoxWillNotLimitOnLargeImage(){
        $this->imageFile->limitToOuterBox(150,150);
        $this->assertEquals(150, $this->imageFile->getWidth());
        $this->assertEquals(100, $this->imageFile->getHeight());
    }

    public function testLimitToOuterBoxWillNotScaleOnHalfLargeImage(){
        $this->imageFile->limitToOuterBox(150,1000);
        $this->assertEquals(150, $this->imageFile->getWidth());
        $this->assertEquals(100, $this->imageFile->getHeight());

    }

    public function testLimitToOuterBoxWillNotScaleOnSmallImage(){
        $this->imageFile->limitToOuterBox(1000,1000);
        $this->assertEquals(300, $this->imageFile->getWidth());
        $this->assertEquals(200, $this->imageFile->getHeight());

    }

    public function testLimitOuterBoxWith0AsArgumentWillAuto(){
        $this->imageFile->limitToOuterBox(150,0);
        $this->assertEquals(150, $this->imageFile->getWidth());
        $this->assertEquals(100, $this->imageFile->getHeight());

    }


    public function testExtendToInnerBoxWillExtendToInnerBox(){
        $this->imageFile->extendToInnerBox(400, 400);
        $this->assertEquals(600,  $this->imageFile->getWidth());
        $this->assertEquals(400, $this->imageFile->getHeight());
    }

    public function testExtendToInnerBoxWillNotExtendToInnerBoxWithSmallBox(){
        $this->imageFile->extendToInnerBox(100, 100);
        $this->assertEquals(300,  $this->imageFile->getWidth());
        $this->assertEquals(200, $this->imageFile->getHeight());
    }


    public function testExtendToInnerBoxWillExtendToInnerBoxWithHalfLargeBox(){
        $this->imageFile->extendToInnerBox(10, 400);
        $this->assertEquals(600,  $this->imageFile->getWidth());
        $this->assertEquals(400, $this->imageFile->getHeight());
    }

    public function testExtendToInnerBoxWillExtendToInnerBoxWith0Argument(){
        $this->imageFile->extendToInnerBox(0, 400);
        $this->assertEquals(600,  $this->imageFile->getWidth());
        $this->assertEquals(400, $this->imageFile->getHeight());
    }

    public function testExtendToOuterBoxWillExtendToOuterBox(){
        $this->imageFile->extendToOuterBox(600, 600);
        $this->assertEquals(600, $this->imageFile->getWidth());
        $this->assertEquals(400, $this->imageFile->getHeight());
    }

    public function testExtendToOuterBoxNotExtendOnHalfLargeBox(){
        $this->imageFile->extendToOuterBox(600, 100);
        $this->assertEquals(300, $this->imageFile->getWidth());
        $this->assertEquals(200, $this->imageFile->getHeight());
    }

    public function testExtendToOuterBoxNotExtendOnSmallBox(){
        $this->imageFile->extendToOuterBox(100, 100);
        $this->assertEquals(300, $this->imageFile->getWidth());
        $this->assertEquals(200, $this->imageFile->getHeight());
    }

    public function testExtendToOuterBoxWillAutoOn0Argument(){
        $this->imageFile->extendToOuterBox(600, 0);
        $this->assertEquals(600, $this->imageFile->getWidth());
        $this->assertEquals(400, $this->imageFile->getHeight());
    }

    public function testCropNonImageDoesNothing(){
        $this->notImageFile->crop(0,0,10,10);
        $this->nonExistingFile->crop(0,0,10,10);

    }

    public function testScaleNonImageDoesNothing(){
        $this->nonExistingFile->forceSize(10,10);
        $this->notImageFile->forceSize(10,10);
    }


    public function testRotateImageWillRotateImage(){
        $this->imageFile->rotate(90);
        $this->assertEquals(200, $this->imageFile->getWidth());
        $this->assertEquals(300, $this->imageFile->getHeight());
    }

    public function testMirrorImage(){
        $this->imageFile->mirrorHorizontal();
        $this->imageFile->mirrorVertical();
    }


    public function tearDown(){
        $this->imageFile->delete();
        $this->notImageFile->delete();
    }

}
