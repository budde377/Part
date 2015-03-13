<?php
namespace ChristianBudde\Part\util\file;

use ChristianBudde\Part\controller\json\ImageFileObjectImpl;
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
        $height = $this->getHeight();
        $width = $this->getWidth();
        return $height == null ? null : $width / $height;
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
        $width_ratio = $width / $this->getWidth();
        $height_ratio = $height / $this->getHeight();
        if ($width_ratio > $height_ratio) {
            return $this->forceSize($this->getWidth() * $width_ratio, 0, $saveAsNewFile);
        }

        return $this->forceSize(0, $this->getHeight() * $height_ratio, $saveAsNewFile);


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
        $width_ratio = $width == 0 ? $height / $this->getHeight() : $width / $this->getWidth();
        $height_ratio = $height == 0 ? $width / $this->getWidth() : $height / $this->getHeight();
        if ($width_ratio < $height_ratio) {
            return $this->forceSize($this->getWidth() * $width_ratio, 0, $saveAsNewFile);
        }

        return $this->forceSize(0, $this->getHeight() * $height_ratio, $saveAsNewFile);

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
        $width = $width <= 0 ? round($this->getRatio() * $height) : $width;
        $height = $height <= 0 ? round($width / $this->getRatio()) : $height;


        return $this->modifyImageHelper(
            function (Imagick $imagick) use ($width, $height) {
                $imagick->resizeimage($width, $height, Imagick::FILTER_CATROM, 1);
            },
            function () use ($width, $height) {
                return $this->newForceImageSizeBasename($width, $height);
            },
            function (ImageFile $file) use ($width, $height) {
                $file->forceSize($width, $height);
            },
            $saveAsNewFile);

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

        return $this->modifyImageHelper(
            function (Imagick $imagick) use ($width, $height, $x, $y) {
                $imagick->cropImage($width, $height, $x, $y);
            },
            function () use ($x, $y, $width, $height) {
                return $this->newCropBasename($x, $y, $width, $height);
            },
            function (ImageFile $file) use ($x, $y, $width, $height) {
                $file->crop($x, $y, $width, $height);
            },
            $saveAsNewFile);

    }

    public function copy($path)
    {
        return ($new_file = parent::copy($path)) == null ? null : new ImageFileImpl($new_file->getAbsoluteFilePath());
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
            return $saveAsNewFile ? $this : null;
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
            return $saveAsNewFile ? $this : null;
        }
        return $this->scaleToInnerBox($width, $height, $saveAsNewFile);
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
            return $saveAsNewFile ? $this : null;
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
            return $saveAsNewFile ? $this : null;
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


        return $this->modifyImageHelper(
            function (Imagick $imagick) use ($degree) {
                $imagick->rotateimage("#000000", $degree);
            },
            function () use ($degree) {
                return $this->newRotationBasename($degree);
            },
            function (ImageFile $file) use ($degree) {
                $file->rotate($degree);
            },
            $saveAsNewFile);
    }

    /**
     * Mirrors the image vertically
     * @param bool $saveAsNewFile
     * @return null | ImageFile
     */
    public function mirrorVertical($saveAsNewFile = false)
    {

        return $this->modifyImageHelper(
            function (Imagick $imagick) {
                $imagick->flopImage();
            },
            function () {
                return $this->newMirrorBasename(1, 0);
            },
            function (ImageFile $file) {
                $file->mirrorVertical();
            },
            $saveAsNewFile);
    }

    private function modifyImageHelper(callable $action, callable $basename_func, callable $new_file_action, $save_as_new_file)
    {
        if ($save_as_new_file) {
            return $this->saveNewFileHelper($basename_func(), $new_file_action);
        }
        $this->updateInfo();
        if ($this->imagick == null) {
            return null;
        }
        $action($this->imagick);
        $this->imagick->writeimage($this->getAbsoluteFilePath());
        $this->imagick = null;
        return null;
    }

    private function saveNewFileHelper($basename, callable $action)
    {
        $file_path = $this->getParentFolder()->getAbsolutePath() . "/" . $basename . "." . $this->getExtension();
        if (file_exists($file_path)) {
            return new ImageFileImpl($file_path);
        }
        $new_file = $this->copy($file_path);
        $action($new_file);
        return $new_file;
    }

    /**
     * Mirrors the image
     * @param bool $saveAsNewFile
     * @return null | ImageFile
     */
    public function mirrorHorizontal($saveAsNewFile = false)
    {
        return $this->modifyImageHelper(
            function (Imagick $imagick) {
                $imagick->flipimage();
            },
            function (){
                return $this->newMirrorBasename(0, 1);
            },
            function (ImageFile $file) {
                $file->mirrorHorizontal();
            },
            $saveAsNewFile);
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