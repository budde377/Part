<?php
namespace ChristianBudde\Part\view\template;
use ChristianBudde\Part\util\file\File;


/**
 * Created by JetBrains PhpStorm.
 * User: budde
 * Date: 5/10/12
 * Time: 10:52 AM
 * To change this template use File | Settings | File Templates.
 */
interface Template
{

    /**
     * @abstract
     * Will set the template to a name provided in config given in constructor
     * @param string $name The name of the template as defined in the config
     * @param string $defaultIfNotFound
     * @return void
     */
    public function setTemplateFromConfig($name, $defaultIfNotFound=null);

    /**
     * @abstract
     * Will open and read some file.
     * @param File $file Some absolute file-path
     * @return void
     */

    public function setTemplate(File $file);

    /**
     * @abstract
     * Will set the current template to this string
     * @param string $string The template as a string
     * @return void
     */
    public function setTemplateFromString($string);



    public function render();

    /**
     * This function will set the initialize flag in the template and not
     * return the result of render.
     * @return void
     */
    public function onlyInitialize();
}
