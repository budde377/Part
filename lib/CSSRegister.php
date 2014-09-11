<?php
namespace ChristianBudde\cbweb;
/**
 * Created by JetBrains PhpStorm.
 * User: budde
 * Date: 5/10/12
 * Time: 10:53 AM
 * To change this template use File | Settings | File Templates.
 */
interface CSSRegister
{

    /**
     * @abstract
     * Register a CSS file to be added to the site
     * @param CSSFile $file
     * @return void
     */
    public function registerCSSFile(CSSFile $file);

    /**
     * @abstract
     * Get an array with the registered files
     * @return array
     */
    public function getRegisteredFiles();

}
