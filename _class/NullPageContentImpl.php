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

    /**
     * @return int | null Returns the time of latest content as timestamp since epoc. If no content, then return null;
     */
    public function latestTime()
    {
        return null;
    }

    /**
     * @param int | null $from List history from a specific time. If null the whole history will be returned.
     * @param int| null $to List history to a specific time.
     * @return array An array containing arrays with keys: "time" and "content"
     */
    public function listContentHistory($from = null, $to = null)
    {
        return array();
    }

    /**
     * @param int $time Seconds since epoch
     * @return String | null Returns content at time or null if no content
     */
    public function getContentAt($time)
    {
        return null;
    }
}