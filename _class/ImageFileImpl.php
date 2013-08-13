<?php
require_once dirname(__FILE__) . '/FileImpl.php';
require_once dirname(__FILE__) . '/../_interface/ImageFile.php';

/**
 * Created by JetBrains PhpStorm.
 * User: budde
 * Date: 8/13/13
 * Time: 5:48 PM
 * To change this template use File | Settings | File Templates.
 */

class ImageFileImpl extends FileImpl implements ImageFile
{

    /** @var  Imagick */
    private $imagick;

    private function updateInfo(){
        try{
            $this->imagick = new Imagick($this->getAbsoluteFilePath());
        } catch(ImagickException $e){
            $this->imagick = null;
        }
    }

    /**
     * @return int | null
     */
    public function getWidth()
    {
        $this->updateInfo();
        return $this->imagick == null?null:$this->imagick->getimagewidth();
    }

    /**
     * @return int | null
     */
    public function getHeight()
    {
        $this->updateInfo();
        return $this->imagick == null? null : $this->imagick->getimageheight();
    }

    /**
     * @return float | null The width / height ratio
     */
    public function getRatio()
    {
        $h = $this->getHeight();
        $w = $this->getWidth();
        return $h == null? null : $w/$h;
    }

    /**
     * Will scale the image to given width, setting the height so that the ratio is maintained.
     * @param int $width
     * @return void
     */
    public function scaleToWidth($width)
    {
        $this->forceSize($width, 0);
    }

    /**
     * Will scale the image to given height, setting the width so that the ratio is maintained.
     * @param int $height
     * @return void
     */
    public function scaleToHeight($height)
    {
        $this->forceSize(0,$height);
    }

    /**
     * Will scale the image such that the inner box is just contained by the image.
     * Two sides of the image will have the same size as the inner box.
     * @param int $width
     * @param int $height
     * @return void
     */
    public function scaleToInnerBox($width, $height)
    {
        $wr = $width/$this->getWidth();
        $hr = $height/$this->getHeight();
        if($wr > $hr){
            $this->forceSize($this->getWidth()*$wr, 0);
        } else {
            $this->forceSize(0, $this->getHeight()*$hr);
        }

    }

    /**
     * Will scale the image such that the image will become the inner box to the box given.
     * @param int $width
     * @param int $height
     * @return void
     */
    public function scaleToOuterBox($width, $height)
    {
        $wr = $width/$this->getWidth();
        $hr = $height/$this->getHeight();
        if($wr < $hr){
            $this->forceSize($this->getWidth()*$wr, 0);
        } else {
            $this->forceSize(0, $this->getHeight()*$hr);
        }
    }

    /**
     * Will force the size of the image, ignoring the ratio.
     * @param int $width
     * @param int $height
     * @return void
     */
    public function forceSize($width, $height)
    {
        $this->updateInfo();
        $this->imagick->resizeimage($width,$height,Imagick::FILTER_CATROM, 1);
        $this->imagick->writeimage($this->getAbsoluteFilePath());
        $this->imagick = null;
    }

    /**
     * Will crop the image. If some of the cropped area is outside of the image,
     * it will not be in the cropped area.
     * @param int $x
     * @param int $y
     * @param int $width
     * @param int $height
     * @return void
     */
    public function crop($x, $y, $width, $height)
    {
        $this->updateInfo();
        $this->imagick->cropimage($width,$height,$x,$y);
        $this->imagick->writeimage($this->getAbsoluteFilePath());
        $this->imagick = null;
    }

    public function copy($path)
    {
        return ($s = parent::copy($path)) == null ? null : new ImageFileImpl($s->getAbsoluteFilePath());
    }


}