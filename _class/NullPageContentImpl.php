<?php
require_once dirname(__FILE__).'/../_interface/PageContent.php';
/**
 * Created by JetBrains PhpStorm.
 * User: budde
 * Date: 25/05/13
 * Time: 22:27
 * To change this template use File | Settings | File Templates.
 */

class NullPageContentImpl implements PageContent{

    /**
     * @param int $from List history from a specific time. If null the whole history will be returned.
     * @return array An array containing arrays with keys: "time" and "content"
     */
    public function listContentHistory($from = null)
    {
        return array();
    }


    /**
     * @param string $content Adds new content. This will be the latest upon addition.
     * @return void
     */
    public function addContent($content)
    {
    }

    /**
     * @return string | null Returns the latest content as a string or null if no content exists.
     */
    public function latestContent()
    {
        return null;
    }
}