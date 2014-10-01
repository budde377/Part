<?php
namespace ChristianBudde\cbweb\model;


/**
 * Created by JetBrains PhpStorm.
 * User: budde
 * Date: 25/05/13
 * Time: 22:27
 * To change this template use File | Settings | File Templates.
 */

class NullContentImpl implements Content{



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
     * @param bool $onlyTimestamps If true the result will be an array of timestamps.
     * @return array An array containing arrays with keys: "time" and "content"
     */
    public function listContentHistory($from = null, $to = null, $onlyTimestamps = false)
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

    /**
     * @return String the latest content
     */
    public function __toString()
    {
        return "";
    }

    /**
     * Searches content for the the string from a given time ($fromTime).
     * The time should be present when available as it would cut down
     * the search time.
     *
     * @param String $string
     * @param int $fromTime Timestamp
     * @return bool TRUE if found else FALSE
     */
    public function containsSubString($string, $fromTime = null)
    {
        return false;
    }

    /**
     * Returns the id
     * @return string
     */
    public function getId()
    {

        return null;
    }

    /**
     * Serializes the object to an instance of JSONObject.
     * @return Object
     */
    public function jsonObjectSerialize()
    {
        return null;
    }


    /**
     * (PHP 5 &gt;= 5.4.0)<br/>
     * Specify data which should be serialized to JSON
     * @link http://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     */
    public function jsonSerialize()
    {
        return null;
    }
}