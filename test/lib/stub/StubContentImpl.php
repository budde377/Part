<?php
namespace ChristianBudde\Part\test\stub;
use ChristianBudde\Part\controller\json\ContentObjectImpl;
use ChristianBudde\Part\model\Content;

/**
 * Created by JetBrains PhpStorm.
 * User: budde
 * Date: 9/15/13
 * Time: 10:41 PM
 * To change this template use File | Settings | File Templates.
 */

class StubContentImpl implements Content
{

    public $id;
    private $content = array();

    /**
     * @param int | null $from List history from a specific time. If null the whole history will be returned.
     * @param int| null $to List history to a specific time.
     * @param bool $onlyTimestamps If true the result will be an array of timestamps.
     * @return array An array containing arrays with keys: "time" and "content"
     */
    public function listContentHistory($from = null, $to = null, $onlyTimestamps = false)
    {
        return $this->content;
    }

    /**
     * @return string | null Returns the latest content as a string or null if no content exists.
     */
    public function latestContent()
    {
        end($this->content);
        return current($this->content)['content'];

    }

    /**
     * @return int | null Returns the time of latest content as timestamp since epoch. If no content, then return null;
     */
    public function latestTime()
    {
        end($this->content);
        return current($this->content)['time'];
    }

    /**
     * @param string $content Adds new content. This will be the latest upon addition.
     * @return int | null Returns null on error else the latest time
     */
    public function addContent($content)
    {
        $t = time();
        $this->content[] = array('time' => $t, 'content' => $content);
        return $t;
    }

    /**
     * @param int $time Seconds since epoch
     * @return Array | null Returns content at time or null if no content
     */
    public function getContentAt($time)
    {
        $content = null;
        foreach ($this->content as $c) {
            if ($c['time'] > $time) {
                return $content;
            }
            $content = $c;
        }
        return $content;
    }

    /**
     * @return String the latest content
     */
    public function __toString()
    {
        return ($c = $this->latestContent()) == null ? "" : $c;

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
        foreach ($this->content as $t => $c) {
            if ($t < $fromTime) {
                continue;
            }

            if (strpos($c, $string) >= 0) {
                return true;
            }
        }
        return false;
    }

    /**
     * Serializes the object to an instance of JSONObject.
     * @return \ChristianBudde\Part\controller\json\Object
     */
    public function jsonObjectSerialize()
    {
        return new ContentObjectImpl($this);
    }

    /**
     * Returns the id
     * @return string
     */
    public function getId()
    {
        return $this->id;
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
        return $this->jsonObjectSerialize()->jsonSerialize();
    }
}