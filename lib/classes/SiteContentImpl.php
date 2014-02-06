<?php
/**
 * Created by PhpStorm.
 * User: budde
 * Date: 10/23/13
 * Time: 12:54 PM
 */

class SiteContentImpl implements Content{

    private $db;
    private $id;
    private $site;

    private $content;
    private $time;
    private $history;

    /** @var  PDOStatement */
    private $preparedAddStatement;


    public function __construct(DB $database, Site $site, $id = "")
    {
        $this->db = $database;
        $this->id = $id;
        $this->site = $site;
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
        $this->initializeHistory();
        if ($this->preparedAddStatement == null) {
            $this->preparedAddStatement = $this->db->getConnection()->prepare("
            INSERT INTO SiteContent (id, `time`, content)
            VALUES (?, ?, ?)");
        }
        $t = $this->site->modify();
        $this->preparedAddStatement->execute(array($this->id, date("Y-m-d H:i:s", $t), $content));
        $this->content = $content;
        $this->history[] = array('content' => $content, 'time' => $t);
        return $t;
    }

    private function initializeHistory()
    {
        if ($this->history != null) {
            return;
        }

        $prep = $this->db->getConnection()->prepare("SELECT content, UNIX_TIMESTAMP(time) AS time FROM SiteContent WHERE id=? ORDER BY time ASC");
        $prep->execute(array($this->id));
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
     * @return array An array containing arrays with keys: "time" and "content"
     */
    public function listContentHistory($from = null, $to = null)
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
}