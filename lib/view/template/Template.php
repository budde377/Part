<?php
namespace ChristianBudde\Part\view\template;
use ChristianBudde\Part\util\file\File;


/**
 * User: budde
 * Date: 5/10/12
 * Time: 10:52 AM
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


    /**
     * Renders the given template with the provided content.
     * Returns the result of the render
     * @param array $context
     * @return string
     */
    public function render(array $context = []);

    /**
     * This function will set the initialize flag in the template and not
     * return the result of render.
     * @param array $context
     */
    public function onlyInitialize(array $context = []);
}
