<?php
namespace ChristianBudde\cbweb\model\page;
use ChristianBudde\cbweb\util\db\DB;


use ChristianBudde\cbweb\controller\json\PageContentObjectImpl;
use PDOStatement;
use PDO;
/**
 * Created by JetBrains PhpStorm.
 * User: budde
 * Date: 25/05/13
 * Time: 22:45
 * To change this template use File | Settings | File Templates.
 */

class PageContentImpl implements PageContent
{

    private $db;
    private $page;
    private $id;

    private $content;
    private $time;
    private $history;

    /** @var  PDOStatement */
    private $preparedAddStatement;

    /** @var  PDOStatement */
    private $preparedSearchStatement;


    public function __construct(DB $database, Page $page, $id = "")
    {
        $this->db = $database;
        $this->page = $page;
        $this->id = $id;
    }


    /**
     * @return string | null Returns the latest content as a string or null if no content exists.
     */
    public function latestContent()
    {
        $this->initializeHistory();
        if ($this->content == null && $this->history != null) {
            $this->content = $this->history[count($this->history) - 1]['content'];
        }

        return $this->content;
    }

    /**
     * @param string $content Adds new content. This will be the latest upon addition.
     * @return int | null Returns null on error else the latest time
     */
    public function addContent($content)
    {
        if (!$this->page->exists()) {
            return null;
        }
        $this->initializeHistory();
        if ($this->preparedAddStatement == null) {
            $this->preparedAddStatement = $this->db->getConnection()->prepare("
            INSERT INTO PageContent (id,page_id,time, content)
            VALUES (?, ?, ?, ?)");
        }
        $t = $this->page->modify();
        $this->preparedAddStatement->execute(array($this->id, $this->page->getID(), date("Y-m-d H:i:s", $t), $content));
        $this->content = $content;
        $this->history[] = array('content' => $content, 'time' => $t);
        return $t;
    }

    private function initializeHistory()
    {
        if ($this->history != null) {
            return;
        }

        $prep = $this->db->getConnection()->prepare("SELECT content, UNIX_TIMESTAMP(time) AS time FROM PageContent WHERE id=? AND page_id = ? ORDER BY time ASC");
        $prep->execute(array($this->id, $this->page->getID()));
        $this->history = $prep->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * @return int | null Returns the time of latest content as timestamp since epoc. If no content, then return null;
     */
    public function latestTime()
    {
        $this->initializeHistory();
        if ($this->time == null && $this->history != null) {
            $this->time = $this->history[count($this->history) - 1]['time'];
        }
        return $this->time;
    }

    /**
     * @param int | null $from List history from a specific time. If null the whole history will be returned.
     * @param int| null $to List history to a specific time.
     * @param bool $onlyTimestamps If true the result will be an array of timestamps.
     * @return array An array containing arrays with keys: "time" and "content"
     */
    public function listContentHistory($from = null, $to = null, $onlyTimestamps = false)
    {
        $this->initializeHistory();
        $result = $this->history;
        if ($from != null) {
            $result = array();
            foreach ($this->history as $e) {
                if ($e['time'] >= $from) {
                    $result[] = $e;
                }
            }
        }
        if ($to != null) {
            $r = $result;
            $result = array();
            foreach ($r as $e) {
                if ($e['time'] <= $to) {
                    $result[] = $e;
                }
            }
        }

        if($onlyTimestamps){
            foreach($result as $k=>$r){
                $result[$k] = $r['time'];
            }
        }

        return $result;
    }

    /**
     * @param int $time Seconds since epoch
     * @return array | null Returns content at time or null if no content
     */
    public function getContentAt($time)
    {
        $this->initializeHistory();
        $h = $this->history;
        $found = false;
        $e = null;
        while (count($h) > 0 && !$found) {
            $e = array_pop($h);
            $found = $e['time'] <= $time;
        }

        return $found ? $e : null;

    }

    /**
     * @return String the latest content
     */
    public function __toString()
    {
        return ($c = $this->latestContent()) == null?"": $c;
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
        if ($this->preparedSearchStatement == null) {
            $this->preparedSearchStatement = $this->db->getConnection()->prepare("
            SELECT time FROM PageContent WHERE content LIKE ? AND page_id = ? AND id = ? AND time >= ?
            ");
        }
        $this->preparedSearchStatement->execute(array("%".$string."%", $this->page->getID(), $this->id, date("Y-m-d H:i:s", $fromTime == null?0:$fromTime)));
        return $this->preparedSearchStatement->rowCount() > 0;
    }

    /**
     * Returns the page instance of which the content is registered.
     * @return Page
     */
    public function getPage()
    {
        return $this->page;
    }

    /**
     * Serializes the object to an instance of JSONObject.
     * @return Object
     */
    public function jsonObjectSerialize()
    {
        return new PageContentObjectImpl($this);
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