<?php
namespace ChristianBudde\cbweb\util\file;

use ChristianBudde\cbweb\controller\json\ImageFileObjectImpl;
use Imagick;
use ImagickException;

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

    private function updateInfo()
    {
        try {
            $this->imagick = new Imagick($this->getAbsoluteFilePath());
        } catch (ImagickException $e) {
            $this->imagick = null;
        }
    }

    /**
     * @return int | null
     */
    public function getWidth()
    {
        $this->updateInfo();
        return $this->imagick == null ? null : $this->imagick->getimagewidth();
    }

    /**
     * @return int | null
     */
    public function getHeight()
    {
        $this->updateInfo();
        return $this->imagick == null ? null : $this->imagick->getimageheight();
    }

    /**
     * @return float | null The width / height ratio
     */
    public function getRatio()
    {
        $h = $this->getHeight();
        $w = $this->getWidth();
        return $h == null ? null : $w / $h;
    }

    /**
     * Will scale the image to given width, setting the height so that the ratio is maintained.
     * @param int $width
     * @param bool $saveAsNewFile
     * @return null | ImageFile
     */
    public function scaleToWidth($width, $saveAsNewFile = false)
    {
        return $this->forceSize($width, 0, $saveAsNewFile);
    }

    /**
     * Will scale the image to given height, setting the width so that the ratio is maintained.
     * @param int $height
     * @param bool $saveAsNewFile
     * @return null | ImageFile
     */
    public function scaleToHeight($height, $saveAsNewFile = false)
    {
        return $this->forceSize(0, $height, $saveAsNewFile);
    }

    /**
     * Will scale the image such that the inner box is just contained by the image.
     * Two sides of the image will have the same size as the inner box.
     * @param int $width
     * @param int $height
     * @param bool $saveAsNewFile
     * @return null | ImageFile
     */
    public function scaleToInnerBox($width, $height, $saveAsNewFile = false)
    {
        $wr = $width / $this->getWidth();
        $hr = $height / $this->getHeight();
        if ($wr > $hr) {
            return $this->forceSize($this->getWidth() * $wr, 0, $saveAsNewFile);
        }

        return $this->forceSize(0, $this->getHeight() * $hr, $saveAsNewFile);


    }

    /**
     * Will scale the image such that the image will become the inner box to the box given.
     * @param int $width
     * @param int $height
     * @param bool $saveAsNewFile
     * @return null | ImageFile
     */
    public function scaleToOuterBox($width, $height, $saveAsNewFile = false)
    {
        $wr = $width == 0 ? $height / $this->getHeight() : $width / $this->getWidth();
        $hr = $height == 0 ? $width / $this->getWidth() : $height / $this->getHeight();
        if ($wr < $hr) {
            return $this->forceSize($this->getWidth() * $wr, 0, $saveAsNewFile);
        }

        return $this->forceSize(0, $this->getHeight() * $hr, $saveAsNewFile);

    }

    /**
     * Will force the size of the image, ignoring the ratio.
     * @param int $width
     * @param int $height
     * @param bool $saveAsNewFile
     * @return null | ImageFile
     */
    public function forceSize($width, $height, $saveAsNewFile = false)
    {
        $width  = $width <= 0?round($this->getRatio()*$height):$width;
        $height = $height <= 0?round($width/$this->getRatio()):$height;

        if ($saveAsNewFile) {
            $fp = $this->getParentFolder()->getAbsolutePath() . '/' . $this->newForceImageSizeBasename($width, $height) . "." . $this->getExtension();
            if (file_exists($fp)) {
                return new ImageFileImpl($fp);
            }
            $f = $this->copy($fp);
            $f->forceSize($width, $height);
            return $f;

        }


        $this->updateInfo();
        if ($this->imagick == null) {
            return null;
        }
        $this->imagick->resizeimage($width, $height, Imagick::FILTER_CATROM, 1);
        $this->imagick->writeimage($this->getAbsoluteFilePath());
        $this->imagick = null;
        return null;
    }

    /**
     * Will crop the image. If some of the cropped area is outside of the image,
     * it will not be in the cropped area.
     * @param int $x
     * @param int $y
     * @param int $width
     * @param int $height
     * @param bool $saveAsNewFile
     * @return null | ImageFile
     */
    public function crop($x, $y, $width, $height, $saveAsNewFile = false)
    {
        if ($saveAsNewFile) {
            $fp = $this->getParentFolder()->getAbsolutePath() . "/" . $this->newCropBasename($x, $y, $width, $height) . "." . $this->getExtension();
            if (file_exists($fp)) {
                return new ImageFileImpl($fp);
            }
            $f = $this->copy($fp);
            $f->crop($x, $y, $width, $height);
            return $f;
        }

        $this->updateInfo();
        if ($this->imagick == null) {
            return null;
        }
        $this->imagick->cropimage($width, $height, $x, $y);
        $this->imagick->writeimage($this->getAbsoluteFilePath());
        $this->imagick = null;
        return null;
    }

    public function copy($path)
    {
        return ($s = parent::copy($path)) == null ? null : new ImageFileImpl($s->getAbsoluteFilePath());
    }


    /**
     * Will limit the image to an outer box. If the image is contained in the box, nothing will happen.
     * @param int $width
     * @param int $height
     * @param bool $saveAsNewFile
     * @return mixed
     */
    public function limitToOuterBox($width, $height, $saveAsNewFile = false)
    {
        if ($this->getWidth() < $width && $this->getHeight() < $height) {
            return $saveAsNewFile?$this:null;
        }
        return $this->scaleToOuterBox($width, $height, $saveAsNewFile);
    }

    /**
     * Will limit the image to an inner box. If the image is contained in the box, nothing will happen.
     * @param int $width
     * @param int $height
     * @param bool $saveAsNewFile
     * @return null | ImageFile
     */
    public function limitToInnerBox($width, $height, $saveAsNewFile = false)
    {
        if ($this->getWidth() < $width || $this->getHeight() < $height) {
            return $saveAsNewFile?$this:null;
        }
        return $this->scaleToInnerBox($width, $height ,$saveAsNewFile);
    }

    /**
     * Will extend image to box. If the box is contained in the image, nothing will happen.
     * @param int $width
     * @param int $height
     * @param bool $saveAsNewFile
     * @return null | ImageFile
     */
    public function extendToInnerBox($width, $height, $saveAsNewFile = false)
    {
        if ($this->getWidth() >= $width && $this->getHeight() >= $height) {
            return $saveAsNewFile?$this:null;
        }
        return $this->scaleToInnerBox($width, $height, $saveAsNewFile);
    }

    /**
     * Will extend image to box, such that at least one side touches the box.
     * If the image is larger than the box on one side, nothing will happen.
     * @param int $width
     * @param int $height
     * @param bool $saveAsNewFile
     * @return null | ImageFile
     */
    public function extendToOuterBox($width, $height, $saveAsNewFile = false)
    {
        if (($this->getWidth() >= $width && $width != 0) || ($this->getHeight() >= $height && $height != 0)) {
            return $saveAsNewFile?$this:null;
        }
        return $this->scaleToOuterBox($width, $height, $saveAsNewFile);

    }

    /**
     * @param $degree Double Number to rotate the image
     * @param bool $saveAsNewFile
     * @return null | ImageFile
     */
    public function rotate($degree, $saveAsNewFile = false)
    {

        if ($saveAsNewFile) {
            $fp = $this->getParentFolder()->getAbsolutePath() . '/' . $this->newRotationBasename($degree) . "." . $this->getExtension();
            if (file_exists($fp)) {
                return new ImageFileImpl($fp);
            }
            $f = $this->copy($fp);
            $f->rotate($degree);
            return $f;
        }
        $this->updateInfo();
        if ($this->imagick == null) {
            return null;
        }
        $this->imagick->rotateimage("#000000", $degree);
        $this->imagick->writeimage($this->getAbsoluteFilePath());
        $this->imagick = null;

        return null;
    }

    /**
     * Mirrors the image vertically
     * @param bool $saveAsNewFile
     * @return null | ImageFile
     */
    public function mirrorVertical($saveAsNewFile = false)
    {
        if ($saveAsNewFile) {
            $fp = $this->getParentFolder()->getAbsolutePath() . "/" . $this->newMirrorBasename(1, 0) . "." . $this->getExtension();
            if (file_exists($fp)) {
                return new ImageFileImpl($fp);
            }
            $f = $this->copy($fp);
            $f->mirrorVertical();
            return $f;
        }
        $this->updateInfo();
        if ($this->imagick == null) {
            return null;
        }
        $this->imagick->flopimage();
        $this->imagick->writeimage($this->getAbsoluteFilePath());
        $this->imagick = null;
        return null;
    }

    /**
     * Mirrors the image
     * @param bool $saveAsNewFile
     * @return null | ImageFile
     */
    public function mirrorHorizontal($saveAsNewFile = false)
    {
        if ($saveAsNewFile) {
            $fp = $this->getParentFolder()->getAbsolutePath() . "/" . $this->newMirrorBasename(0, 1) . "." . $this->getExtension();
            if (file_exists($fp)) {
                return new ImageFileImpl($fp);
            }
            $f = $this->copy($fp);
            $f->mirrorHorizontal();
            return $f;
        }
        $this->updateInfo();
        if ($this->imagick == null) {
            return null;
        }
        $this->imagick->flipimage();
        $this->imagick->writeimage($this->getAbsoluteFilePath());
        $this->imagick = null;
        return null;
    }

    private function newRotationBasename($degree)
    {
        if (preg_match("/^(.+-R_)([0-9]+)(.*)$/", $this->getBasename(), $match)) {
            return $match[1] . (($match[2] + $degree) % 360) . $match[3];
        }
        $degree = $degree % 360;
        return $this->getBasename() . "-R_$degree";
    }

    private function newForceImageSizeBasename($width, $height)
    {

        if (preg_match("/-S((_[0-9]+_[0-9]+)+)/", $this->getBasename(), $match, PREG_OFFSET_CAPTURE)) {
            $offset = $match[1][1];
            $oldSizeString = $match[1][0];
            $basename = $this->getBasename();
            return substr($basename, 0, $offset) . $oldSizeString . "_{$width}_$height" . substr($basename, $offset + strlen($oldSizeString));

        }
        return $this->getBasename() . "-S_{$width}_$height";
    }

    private function newMirrorBasename($vertical, $horizontal)
    {
        if (preg_match('/(.*-M_)([01])_([01])(.*)/', $this->getBasename(), $match)) {
            $vertical = ($vertical + $match[2]) % 2;
            $horizontal = ($horizontal + $match[3]) % 2;
            return $match[1] . $vertical . "_" . $horizontal . $match[4];
        }
        return $this->getBasename() . "-M_" . $vertical . "_" . $horizontal;
    }

    private function newCropBasename($x, $y, $width, $height)
    {
        if (preg_match("/-C((_[0-9]+_[0-9]+_[0-9]+_[0-9]+)+)/", $this->getBasename(), $match, PREG_OFFSET_CAPTURE)) {
            $offset = $match[1][1];
            $oldSizeString = $match[1][0];
            $basename = $this->getBasename();
            return substr($basename, 0, $offset) . $oldSizeString . "_" . $x . "_" . $y . "_" . $width . "_" . $height . substr($basename, $offset + strlen($oldSizeString));

        }
        return $this->getBasename() . "-C_" . $x . "_" . $y . "_" . $width . "_" . $height;
    }

    public function jsonObjectSerialize()
    {
        return new ImageFileObjectImpl($this);
    }


}