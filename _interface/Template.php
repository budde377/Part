<?php
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
     * @return void
     */
    public function setTemplateFromConfig($name);

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
}
