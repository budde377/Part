<?php
namespace ChristianBudde\cbweb;

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


    /**
     * This will search the content of each content
     * and return an array containing all contents matching
     * the search string.
     *
     * @param String $string
     * @param int $time Will limit the search to those contents after given timestamp
     * @return array
     */
    public function searchLibrary($string, $time= null);


} 