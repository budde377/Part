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
    private $fileToDelete;
    /** @var  ImageFileImpl */
    private $nonExistingFile;
    /** @var  Folder  */
    private $folder;

    public function setUp(){
        $this->folder =new FolderImpl("/tmp/".uniqid());
        $this->folder->create();
        $file = new ImageFileImpl(dirname(__FILE__).'/stubs/imageFileStub300x200.png');
        $this->imageFile = $file->copy($this->folder->getAbsolutePath().'/imgStub.png');

        $file = new ImageFileImpl(dirname(__FILE__).'/stubs/fileStub');
        $this->notImageFile = $file->copy($this->folder->getAbsolutePath().'/fileStub2');
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

    public function testForceSizeWillForceSizeAndSaveAsNew(){

        $this->fileToDelete = $f = $this->imageFile->forceSize(200,100, true);
        $this->assertEquals($f->getParentFolder()->getAbsolutePath(), $this->imageFile->getParentFolder()->getAbsolutePath());

        $this->assertEquals(200, $f->getWidth());
        $this->assertEquals(100,$f->getHeight());
        $this->assertEquals(2, $f->getRatio());
        $this->assertEquals($this->imageFile->getBasename().'-S_200_100', $f->getBasename());

        $this->assertNotEquals(200, $this->imageFile->getWidth());
        $this->assertNotEquals(100,$this->imageFile->getHeight());
        $this->assertNotEquals(2, $this->imageFile->getRatio());

    }

    public function testForceSizeWillForceSizeAndSaveAsNewAndUpdateName(){

        $f = $this->imageFile->forceSize(200,100, true);
        $f = $f->forceSize(400, 400, true);
        $this->assertEquals($this->imageFile->getExtension(), $f->getExtension());

        $this->assertEquals($f->getBasename(),$this->imageFile->getBasename().'-S_200_100_400_400');

    }

    public function testForceSizeWillForceSizeAndSaveAsNewAndUpdateNameNotOverwrite(){

        $f = $this->imageFile->forceSize(200,100, true);
        $f->forceSize(10,10);
        $f = $this->imageFile->forceSize(200,100, true);
        $this->assertEquals(10,$f->getWidth());


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

    public function testCropWillCropImageAndSaveAsNew(){
        $f = $this->imageFile->crop(40,40,100,100, true);
        $this->assertNotEquals(100, $this->imageFile->getWidth());
        $this->assertNotEquals(100, $this->imageFile->getHeight());
        $this->assertEquals(100, $f->getWidth());
        $this->assertEquals(100, $f->getHeight());
        $this->assertEquals($this->imageFile->getBasename()."-C_40_40_100_100", $f->getBasename());
        $this->assertEquals($this->imageFile->getExtension(), $f->getExtension());
        $this->assertEquals($this->imageFile->getParentFolder()->getAbsolutePath(), $f->getParentFolder()->getAbsolutePath());
    }
    public function testCropWillCropImageAndSaveAsNewMultipleTimes(){
        $f = $this->imageFile->crop(40,40,100,100, true);
        $f = $f->crop(40,40,10,10, true);

        $this->assertEquals(10, $f->getWidth());
        $this->assertEquals(10, $f->getHeight());
        $this->assertEquals($this->imageFile->getBasename()."-C_40_40_100_100_40_40_10_10", $f->getBasename());
    }
    public function testCropWillCropImageAndSaveAsNewMultipleTimesDoesNotOverwrite(){
        $f = $this->imageFile->crop(40,40,100,100, true);
        $f->crop(40,40,10,10);

        $f = $this->imageFile->crop(40,40,100,100,true);

        $this->assertEquals(10, $f->getWidth());
        $this->assertEquals(10, $f->getHeight());
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

    public function testRotateAndSaveAsNewFileWillRotateAndSave(){
        $f = $this->imageFile->rotate(90, true);
        $this->assertEquals(300, $this->imageFile->getWidth());
        $this->assertEquals(200, $this->imageFile->getHeight());
        $this->assertEquals(200, $f->getWidth());
        $this->assertEquals(300, $f->getHeight());
        $this->assertEquals($this->imageFile->getBasename().'-R_90', $f->getBasename());
        $this->assertEquals($this->imageFile->getExtension(), $f->getExtension());

        $this->assertEquals($f->getParentFolder()->getAbsolutePath(), $this->imageFile->getParentFolder()->getAbsolutePath());
    }


    public function testRotateWillNotRotateWhenNewFileExists(){
        $f = $this->imageFile->rotate(90, true);
        $f->crop(0,0,10,10);
        $f = $this->imageFile->rotate(90, true);
        $this->assertEquals(10, $f->getWidth());

    }
    public function testRotateTwiceWillChangeName(){
        $f = $this->imageFile->rotate(90, true);
        $f = $f->rotate(90, true);
        $this->assertEquals(300, $f->getWidth());
        $this->assertEquals(200, $f->getHeight());
        $this->assertEquals($this->imageFile->getBasename().'-R_180', $f->getBasename());

    }
    public function testRotateMoreThan359WillBeModulo(){
        $f = $this->imageFile->rotate(400, true);
        $this->assertEquals($this->imageFile->getBasename().'-R_40', $f->getBasename());

    }    public function testRotateMoreThan359OverTimeWillBeModulo(){
        $f = $this->imageFile->rotate(200, true);
        $f = $f->rotate(200, true);
        $this->assertEquals($this->imageFile->getBasename().'-R_40', $f->getBasename());

    }
    public function testRotate360WillBeModulo(){
        $f = $this->imageFile->rotate(360, true);
        $this->assertEquals($this->imageFile->getBasename().'-R_0', $f->getBasename());

    }

    public function testMirrorImage(){
        $this->imageFile->mirrorHorizontal();
        $this->imageFile->mirrorVertical();
    }


    public function testMirrorImageSaveAsNew(){
        $f = $this->imageFile->mirrorHorizontal(true);
        $this->assertEquals($this->imageFile->getBasename()."-M_0_1", $f->getBasename());
        $this->assertEquals($this->imageFile->getExtension(), $f->getExtension());
        $f = $this->imageFile->mirrorVertical(true);
        $this->assertEquals($this->imageFile->getBasename()."-M_1_0", $f->getBasename());
        $this->assertEquals($this->imageFile->getExtension(), $f->getExtension());
    }

    public function testMirrorImageSaveAsNewWillNotOverwrite(){
        $f = $this->imageFile->mirrorHorizontal(true);
        $f->crop(0,0,10,10);
        $f = $this->imageFile->mirrorHorizontal(true);
        $this->assertEquals(10, $f->getWidth());
        $f = $this->imageFile->mirrorVertical(true);
        $f->crop(0,0,10,10);
        $f = $this->imageFile->mirrorVertical(true);
        $this->assertEquals(10, $f->getWidth());
    }

    public function testMirrorMultipleChangesName(){
        $f = $this->imageFile->mirrorHorizontal(true);
        $f = $f->mirrorVertical(true);
        $this->assertEquals($this->imageFile->getBasename()."-M_1_1", $f->getBasename());
        /** @var ImageFile $f */
        $f = $f->mirrorVertical(true);
        $this->assertEquals($this->imageFile->getBasename()."-M_0_1", $f->getBasename());
    }


    public function testScaleToWidthSavesNewFile(){
        $f = $this->imageFile->scaleToWidth(10, true);
        $this->assertEquals(10, $f->getWidth());
        $this->assertNotEquals(10, $this->imageFile->getWidth());

    }

    public function testScaleToHeightSavesNewFile(){
        $f = $this->imageFile->scaleToHeight(10, true);
        $this->assertEquals(10, $f->getHeight());
        $this->assertNotEquals(10, $this->imageFile->getHeight());
    }

    public function testScaleToInnerBoxSavesNewFile(){
        $f = $this->imageFile->scaleToInnerBox(10, 10, true);
        $this->assertEquals(10, $f->getHeight());
        $this->assertNotEquals(10, $this->imageFile->getHeight());
        $this->assertEquals(15, $f->getWidth());
        $this->assertNotEquals(15, $this->imageFile->getWidth());
    }
    public function testScaleToOuterBoxSavesNewFile(){
        $f = $this->imageFile->scaleToOuterBox(15, 15, true);
        $this->assertEquals(10, $f->getHeight());
        $this->assertNotEquals(10, $this->imageFile->getHeight());
        $this->assertEquals(15, $f->getWidth());
        $this->assertNotEquals(15, $this->imageFile->getWidth());
    }

    public function testLimitToInnerBoxSavesNewFile(){
        $f = $this->imageFile->limitToInnerBox(10, 10, true);
        $this->assertEquals(10, $f->getHeight());
        $this->assertNotEquals(10, $this->imageFile->getHeight());
        $this->assertEquals(15, $f->getWidth());
        $this->assertNotEquals(15, $this->imageFile->getWidth());
    }

    public function testLimitToOuterBoxSavesNewFile(){
        $f = $this->imageFile->limitToOuterBox(15, 15, true);
        $this->assertEquals(10, $f->getHeight());
        $this->assertNotEquals(10, $this->imageFile->getHeight());
        $this->assertEquals(15, $f->getWidth());
        $this->assertNotEquals(15, $this->imageFile->getWidth());
    }

    public function testLimitToOuterAndInnerReturnsInstanceIfSaveAsNewFileAndNothingToDo(){
        $f = $this->imageFile->limitToOuterBox(1000, 1000, true);
        $this->assertTrue($this->imageFile === $f);
        $f = $this->imageFile->limitToInnerBox(1000, 1000, true);
        $this->assertTrue($this->imageFile === $f);
    }

    public function testExtendToInnerBoxSavesNewFile(){
        $f = $this->imageFile->extendToInnerBox(1000, 1000, true);
        $this->assertEquals(1000, $f->getHeight());
        $this->assertNotEquals(1000, $this->imageFile->getHeight());
        $this->assertEquals(1500, $f->getWidth());
        $this->assertNotEquals(1500, $this->imageFile->getWidth());
    }

    public function testExtendToOuterBoxSavesNewFile(){
        $f = $this->imageFile->extendToOuterBox(1500, 1500, true);
        $this->assertEquals(1000, $f->getHeight());
        $this->assertNotEquals(1000, $this->imageFile->getHeight());
        $this->assertEquals(1500, $f->getWidth());
        $this->assertNotEquals(1500, $this->imageFile->getWidth());
    }

    public function testExtendToOuterAndInnerReturnsInstanceIfSaveAsNewFileAndNothingToDo(){
        $f = $this->imageFile->extendToInnerBox(10, 10, true);
        $this->assertTrue($this->imageFile === $f);
        $f = $this->imageFile->extendToInnerBox(10, 10, true);
        $this->assertTrue($this->imageFile === $f);
    }



    public function tearDown(){
        $this->folder->delete(Folder::DELETE_FOLDER_RECURSIVE);
    }

}
