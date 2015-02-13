<?php
namespace ChristianBudde\Part\util\file;


/**
 * Created by JetBrains PhpStorm.
 * User: budde
 * Date: 8/13/13
 * Time: 5:49 PM
 * To change this template use File | Settings | File Templates.
 */

interface ImageFile extends File{

    /**
     * @return int | null
     */
    public function getWidth();

    /**
     * @return int | null
     */
    public function getHeight();

    /**
     * @return float | null The width / height ratio
     */
    public function getRatio();

    /**
     * Will scale the image to given width, setting the height so that the ratio is maintained.
     * @param int $width
     * @param bool $saveAsNewFile
     * @return null | ImageFile
     */
    public function scaleToWidth($width, $saveAsNewFile = false);

    /**
     * Will scale the image to given height, setting the width so that the ratio is maintained.
     * @param int $height
     * @param bool $saveAsNewFile
     * @return null | ImageFile
     */
    public function scaleToHeight($height, $saveAsNewFile = false);

    /**
     * Will scale the image such that the inner box is just contained by the image.
     * Two sides of the image will have the same size as the inner box.
     * @param int $width
     * @param int $height
     * @param bool $saveAsNewFile
     * @return null | ImageFile
     */
    public function scaleToInnerBox($width, $height, $saveAsNewFile = false);

    /**
     * Will scale the image such that the image will become the inner box to the box given.
     * @param int $width
     * @param int $height
     * @param bool $saveAsNewFile
     * @return null | ImageFile
     */
    public function scaleToOuterBox($width, $height, $saveAsNewFile = false);

    /**
     * Will limit the image to an inner box. If the image is contained in the box, nothing will happen.
     * @param int $width
     * @param int $height
     * @param bool $saveAsNewFile
     * @return null | ImageFile
     */
    public function limitToInnerBox($width, $height, $saveAsNewFile = false);

    /**
     * Will limit the image to an outer box. If the image is contained in the box, nothing will happen.
     * @param int $width
     * @param int $height
     * @param bool $saveAsNewFile
     * @return mixed
     */
    public function limitToOuterBox($width, $height, $saveAsNewFile = false);

    /**
     * Will extend image to box. If the box is contained in the image, nothing will happen.
     * @param int $width
     * @param int $height
     * @param bool $saveAsNewFile
     * @return null | ImageFile
     */
    public function extendToInnerBox($width, $height, $saveAsNewFile = false);


    /**
     * Will extend image to box, such that at least one side touches the box.
     * If the image is larger than the box on one side, nothing will happen.
     * @param int $width
     * @param int $height
     * @param bool $saveAsNewFile
     * @return null | ImageFile
     */
    public function extendToOuterBox($width, $height, $saveAsNewFile = false);


    /**
     * Will force the size of the image, ignoring the ratio.
     * @param int $width
     * @param int $height
     * @param bool $saveAsNewFile
     * @return null | ImageFile
     */
    public function forceSize($width, $height, $saveAsNewFile = false);

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
    public function crop($x, $y, $width, $height, $saveAsNewFile = false);


    /**
     * @param $degree Double Number to rotate the image
     * @param bool $saveAsNewFile
     * @return null | ImageFile
     */
    public function rotate($degree, $saveAsNewFile = false);

    /**
     * Mirrors the image vertically
     * @param bool $saveAsNewFile
     * @return null | ImageFile
     */
    public function mirrorVertical($saveAsNewFile = false);

    /**
     * Mirrors the image
     * @param bool $saveAsNewFile
     * @return null | ImageFile
     */
    public function mirrorHorizontal($saveAsNewFile = false);

}