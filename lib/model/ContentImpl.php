<?php
/**
 * Created by PhpStorm.
 * User: budde
 * Date: 3/13/15
 * Time: 9:42 AM
 */

namespace ChristianBudde\Part\model;


use PDO;
use PDOStatement;

abstract class ContentImpl implements Content
{

    private $addContentStm;
    private $latestContentStm;
    private $listContentHistStm;
    private $listContentHistContentStm;
    private $getContentAtStm;
    private $containsSubStrStm;
    private $latestContent;
    private $modifyStrategy;
    private $id;
    private $latestTime;

    function __construct(
        $id,
        callable $modifyStrategy,
        PDOStatement $addContentStm,
        PDOStatement $latestContentStm,
        PDOStatement $listContentHistStm,
        PDOStatement $listContentHistContentStm,
        PDOStatement $getContentAtStm,
        PDOStatement $containsSubStrStm)
    {
        $this->id = $id;
        $this->modifyStrategy = $modifyStrategy;
        $this->addContentStm = $addContentStm;
        $this->latestContentStm = $latestContentStm;
        $this->listContentHistStm = $listContentHistStm;
        $this->listContentHistContentStm = $listContentHistContentStm;
        $this->getContentAtStm = $getContentAtStm;
        $this->containsSubStrStm = $containsSubStrStm;
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

        $modStrategy = $this->modifyStrategy;
        $time = $modStrategy();
        $this->addContentStm->bindValue(":id", $this->id);
        $this->addContentStm->bindValue(":time", $time);
        $this->addContentStm->bindValue(":content", $content);
        $this->addContentStm->execute();
        $this->latestContent = $content;
        return $this->latestTime = $time;
    }

    private function initializeLatest()
    {
        if ($this->latestContentStm == null) {
            return;
        }

        $this->latestContentStm->bindValue(":id", $this->id);
        $this->latestContentStm->execute();
        $result = $this->latestContentStm->fetchAll(PDO::FETCH_ASSOC);
        if (!count($result)) {
            return;
        }
        $result = $result[0];

        $this->latestContent = $result['content'];
        $this->latestTime = $result['time'];
        $this->latestContentStm = null;

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
        $from = $from == null ? 0 : $from;
        $to = $to == null ? time() : $to;

        $prep_statement = $onlyTimestamps ? $this->listContentHistStm : $this->listContentHistContentStm;
        $prep_statement->bindValue(":id", $this->id);
        $prep_statement->bindValue(":from", $from);
        $prep_statement->bindValue(":to", $to);
        $prep_statement->execute();
        return $onlyTimestamps ? $prep_statement->fetchAll(PDO::FETCH_COLUMN, 0) : $prep_statement->fetchAll(PDO::FETCH_ASSOC);


    }

    /**
     * @param int $time Seconds since epoch
     * @return array | null Returns content at time or null if no content
     */
    public function getContentAt($time)
    {

        $this->getContentAtStm->bindValue(":id", $this->id);
        $this->getContentAtStm->bindValue(":time", $time);
        $this->getContentAtStm->execute();
        return count($result = $this->getContentAtStm->fetchAll(PDO::FETCH_ASSOC)) ? $result[0] : null;

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

        $this->containsSubStrStm->bindValue(":like", "%" . $string . "%");
        $this->containsSubStrStm->bindValue(":id", $this->id);
        $this->containsSubStrStm->bindValue(":time", $fromTime == null ? 0 : $fromTime);
        $this->containsSubStrStm->execute();
        return $this->containsSubStrStm->rowCount() > 0;
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