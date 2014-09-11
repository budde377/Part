<?php
namespace ChristianBudde\cbweb;
/**
 * Created by JetBrains PhpStorm.
 * User: budde
 * Date: 18/01/13
 * Time: 11:39
 */
interface DartRegister
{
    /**
     * Register a Dart file to be added to the site
     * @param DartFile $file
     * @return void
     */
    public function registerDartFile(DartFile $file);

    /**
     * Returns an array containing the registered Dart files
     * @return array
     */
    public function getRegisteredFiles();
}
