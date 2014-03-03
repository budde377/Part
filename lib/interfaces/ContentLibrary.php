<?php
/**
 * Created by PhpStorm.
 * User: budde
 * Date: 3/3/14
 * Time: 9:31 PM
 */

interface ContentLibrary {

    /**
     * This will list site content.
     * It the timestamp is given, the latest time will be newer
     * than the timestamp.
     *
     * @param int $time A Unix timestamp
     * @return array A array of PageContent.
     */

    public function listContents($time = 0);


    /**
     * This will return and reuse a instance of content related to the given id.
     *
     * @param string $id
     * @return Content
     */
    public function getContent($id = "");



} 