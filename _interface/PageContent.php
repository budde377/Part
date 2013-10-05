<?php
/**
 * Created by JetBrains PhpStorm.
 * User: budde
 * Date: 25/05/13
 * Time: 22:13
 * To change this template use File | Settings | File Templates.
 */

interface PageContent {


    /**
     * @param int | null $from List history from a specific time. If null the whole history will be returned.
     * @param int| null $to List history to a specific time.
     * @return array An array containing arrays with keys: "time" and "content"
     */
    public function listContentHistory($from = null, $to = null);

    /**
     * @return string | null Returns the latest content as a string or null if no content exists.
     */
    public function latestContent();

    /**
     * @return int | null Returns the time of latest content as timestamp since epoc. If no content, then return null;
     */
    public function latestTime();

    /**
     * @param string $content Adds new content. This will be the latest upon addition.
     * @return int | null Returns null on error else the latest time
     */
    public function addContent($content);

    /**
     * @param int $time Seconds since epoch
     * @return Array | null Returns content at time or null if no content
     */
    public function getContentAt($time);

}