<?php
require_once dirname(__FILE__).'/File.php';
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
     * @return void
     */
    public function scaleToWidth($width);

    /**
     * Will scale the image to given height, setting the width so that the ratio is maintained.
     * @param int $height
     * @return void
     */
    public function scaleToHeight($height);

    /**
     * Will scale the image such that the inner box is just contained by the image.
     * Two sides of the image will have the same size as the inner box.
     * @param int $width
     * @param int $height
     * @return void
     */
    public function scaleToInnerBox($width, $height);

    /**
     * Will scale the image such that the image will become the inner box to the box given.
     * @param int $width
     * @param int $height
     * @return void
     */
    public function scaleToOuterBox($width, $height);

    /**
     * Will limit the image to an inner box. If the image is contained in the box, nothing will happen.
     * @param int $width
     * @param int $height
     * @return void
     */
    public function limitToInnerBox($width, $height);

    /**
     * Will limit the image to an outer box. If the image is contained in the box, nothing will happen.
     * @param int $width
     * @param int $height
     * @return mixed
     */
    public function limitToOuterBox($width, $height);

    /**
     * Will extend image to box. If the box is contained in the image, nothing will happen.
     * @param int $width
     * @param int $height
     * @return void
     */
    public function extendToInnerBox($width, $height);


    /**
     * Will extend image to box, such that at least one side touches the box.
     * If the image is larger than the box on one side, nothing will happen.
     * @param int $width
     * @param int $height
     * @return void
     */
    public function extendToOuterBox($width, $height);


    /**
     * Will force the size of the image, ignoring the ratio.
     * @param int $width
     * @param int $height
     * @return void
     */
    public function forceSize($width, $height);

    /**
     * Will crop the image. If some of the cropped area is outside of the image,
     * it will not be in the cropped area.
     * @param int $x
     * @param int $y
     * @param int $width
     * @param int $height
     * @return void
     */
    public function crop($x, $y, $width, $height);


    /**
     * @param $degree Double Number to rotate the image
     * @return void
     */
    public function rotate($degree);

    /**
     * Mirrors the image vertically
     * @return void
     */
    public function mirrorVertical();

    /**
     * Mirrors the image
     * @return void
     */
    public function mirrorHorizontal();

}