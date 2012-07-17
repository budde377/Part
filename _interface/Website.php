<?php
/**
 * Created by JetBrains PhpStorm.
 * User: budde
 * Date: 5/10/12
 * Time: 10:51 AM
 *
 */
interface Website
{

    const OUTPUT_AJAX = 'ajax';
    const OUTPUT_XHTML = 'xhtml';

    const WEBSITE_SCRIPT_TYPE_PRESCRIPT = 1;
    const WEBSITE_SCRIPT_TYPE_POSTSCRIPT = 2;

    /**
     * Generate site and output it in browser.
     * @abstract
     */
    public function generateSite();
}
