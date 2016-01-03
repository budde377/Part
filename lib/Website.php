<?php
namespace ChristianBudde\Part;
/**
 * Created by JetBrains PhpStorm.
 * User: budde
 * Date: 5/10/12
 * Time: 10:51 AM
 *
 */
interface Website
{
    /**
     * Generate site and output it in browser.
     * @abstract
     */
    public function generateSite();
}
