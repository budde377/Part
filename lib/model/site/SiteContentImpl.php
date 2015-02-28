<?php
namespace ChristianBudde\Part\model\site;

use ChristianBudde\Part\controller\ajax\type_handler\TypeHandler;
use ChristianBudde\Part\controller\json\SiteContentObjectImpl;
use ChristianBudde\Part\util\db\DB;
use PDO;
use PDOStatement;


/**
 * Created by PhpStorm.
 * User: budde
 * Date: 10/23/13
 * Time: 12:54 PM
 */
class SiteContentImpl implements SiteContent
{

    private $db;
    private $id;
    private $site;

    private $latestContent;
    private $latestTime;

    /** @var  PDOStatement */
    private $preparedAddStatement;
    /** @var  PDOStatement */
    private $preparedSearchStatement;
    private $latestHasBeenSetUp = false;
    /** @var  PDOStatement */
    private $getContentAtPreparedStatement;


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
        $this->initializeLatest();

        return $this->latestContent;
    }

    /**
     * @param string $content Adds new content. This will be the latest upon addition.
     * @return int | null Returns null on error else the latest time
     */
    public function addContent($content)
    {
        if ($this->preparedAddStatement == null) {
            $this->preparedAddStatement = $this->db->getConnection()->prepare("
            INSERT INTO SiteContent (id, `time`, content)
            VALUES (?, ?, ?)");
        }
        $t = $this->site->modify();
        $this->preparedAddStatement->execute(array($this->id, date("Y-m-d H:i:s", $t), $content));
        $this->latestContent = $content;
        $this->latestTime = $t;
        return $t;
    }

    private function initializeLatest()
    {
        if ($this->latestHasBeenSetUp) {
            return;
        }
        $this->latestHasBeenSetUp = true;
        $prep = $this->db->getConnection()->prepare("SELECT content, UNIX_TIMESTAMP(time) AS time FROM SiteContent WHERE id=? ORDER BY time DESC LIMIT 1");
        $prep->execute([$this->id]);
        $result = $prep->fetchAll(PDO::FETCH_ASSOC);
        if (!count($result)) {
            return;
        }
        $result = $result[0];

        $this->latestContent = $result['content'];
        $this->latestTime = $result['time'];

    }

    /**
     * @return int | null Returns the time of latest content as timestamp since epoc. If no content, then return null;
     */
    public function latestTime()
    {
        $this->initializeLatest();
        return $this->latestTime;
    }

    /**
     * @param int | null $from List history from a specific time. If null the whole history will be returned.
     * @param int| null $to List history to a specific time.
     * @param bool $onlyTimestamps If true the result will be an array of timestamps.
     * @return array An array containing arrays with keys: "time" and "content"
     */
    public function listContentHistory($from = null, $to = null, $onlyTimestamps = false)
    {
        $from = $from == null?0:$from;
        $to = $to == null?time():$to;

        if($onlyTimestamps){
            $prep = $this->db->getConnection()
                ->prepare("SELECT UNIX_TIMESTAMP(time) as time FROM SiteContent WHERE id=? AND ? <= UNIX_TIMESTAMP(time) AND UNIX_TIMESTAMP(time) <= ?");
        } else {
            $prep = $this->db->getConnection()
                ->prepare("SELECT content,UNIX_TIMESTAMP(time) as time  FROM SiteContent WHERE id=? AND ? <= UNIX_TIMESTAMP(time) AND UNIX_TIMESTAMP(time) <= ?");
        }
        $prep->execute([$this->id, $from, $to]);
        return $onlyTimestamps?$prep->fetchAll(PDO::FETCH_COLUMN, 0):$prep->fetchAll(PDO::FETCH_ASSOC);


    }

    /**
     * @param int $time Seconds since epoch
     * @return array | null Returns content at time or null if no content
     */
    public function getContentAt($time)
    {
        if($this->getContentAtPreparedStatement == null){
            $this->getContentAtPreparedStatement = $this->db->getConnection()
                ->prepare("SELECT content,UNIX_TIMESTAMP(time) as time FROM SiteContent WHERE id=? AND time <= FROM_UNIXTIME(?) ORDER BY time DESC LIMIT 1");
        }

        $this->getContentAtPreparedStatement->execute([$this->id, $time]);

        return count($r = $this->getContentAtPreparedStatement->fetchAll(PDO::FETCH_ASSOC))?$r[0]:null;

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
        if ($this->preparedSearchStatement == null) {
            $this->preparedSearchStatement = $this->db->getConnection()->prepare("
            SELECT time FROM SiteContent WHERE content LIKE ? AND id = ? AND time >= ?
            ");
        }
        $this->preparedSearchStatement->execute(array("%" . $string . "%", $this->id, date("Y-m-d H:i:s", $fromTime == null ? 0 : $fromTime)));
        return $this->preparedSearchStatement->rowCount() > 0;
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
     * Serializes the object to an instance of JSONObject.
     * @return Object
     */
    public function jsonObjectSerialize()
    {
        return new SiteContentObjectImpl($this);
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

    /**
     * @return TypeHandler
     */
    public function generateTypeHandler()
    {
        // TODO: Implement generateTypeHandler() method.
    }
}