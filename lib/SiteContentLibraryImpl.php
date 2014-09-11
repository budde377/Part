<?php
namespace ChristianBudde\cbweb;
use PDO;
/**
 * Created by PhpStorm.
 * User: budde
 * Date: 3/3/14
 * Time: 10:53 PM
 */
class SiteContentLibraryImpl implements SiteContentLibrary
{


    /** @var  Site */
    private $site;
    /** @var  DB */
    private $db;
    private $listContentPreparedStatement;
    private $idArray;
    private $searchLibraryPreparedStatement;


    function __construct(DB $db, Site $site)
    {
        $this->site = $site;
        $this->db = $db;
    }


    /**
     * This will list site content.
     * It the timestamp is given, the latest time will be newer
     * than the timestamp.
     *
     * @param int $time A Unix timestamp
     * @return array A array of PageContent.
     */
    public function listContents($time = 0)
    {
        $this->setUpList();
        return array_filter($this->idArray, function (SiteContentImpl $content) use ($time) {
            return $content->latestTime() >= $time;
        });
    }

    /**
     * This will return and reuse a instance of content related to the given id.
     *
     * @param string $id
     * @return Content
     */
    public function getContent($id = "")
    {
        $this->setUpList();
        return isset($this->idArray[$id]) ?
            $this->idArray[$id] :
            $this->idArray[$id] = new SiteContentImpl($this->db, $this->site, $id);

    }

    private function setUpList()
    {

        if ($this->listContentPreparedStatement == null) {
            $this->listContentPreparedStatement =
                $this->db->getConnection()->prepare("SELECT DISTINCT id FROM SiteContent");
            $this->listContentPreparedStatement->execute();
            foreach ($this->listContentPreparedStatement->fetchAll(PDO::FETCH_ASSOC) as $val) {
                $this->idArray[$val["id"]] = new SiteContentImpl($this->db, $this->site, $val["id"]);
            }

        }
    }


    /**
     * This will search the content of each content
     * and return an array containing all contents matching
     * the search string.
     *
     * @param String $string
     * @param int $time Will limit the search to those contents after given timestamp
     * @return array
     */
    public function searchLibrary($string, $time = null)
    {
        $this->setUpList();
        if ($this->searchLibraryPreparedStatement == null) {
            $this->searchLibraryPreparedStatement = $this->db->getConnection()->
                prepare("SELECT DISTINCT id FROM SiteContent WHERE content LIKE ? AND time >= ? ");
        }
        $this->searchLibraryPreparedStatement->execute(array("%$string%",
            date("Y-m-d H:i:s", $time == null?0:$time)));
        $retArray = array();
        foreach ($this->searchLibraryPreparedStatement->fetchAll(PDO::FETCH_ASSOC) as $val) {
            $id = $val["id"];
            if (!isset($this->idArray[$id])) {
                continue;
            }
            $retArray[$id] = $this->idArray[$id];
        }

        return $retArray;
    }
}