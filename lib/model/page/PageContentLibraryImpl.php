<?php
namespace ChristianBudde\Part\model\page;
use ChristianBudde\Part\controller\ajax\TypeHandler;
use ChristianBudde\Part\model\Content;
use ChristianBudde\Part\util\db\DB;
use PDO;

/**
 * Created by PhpStorm.
 * User: budde
 * Date: 3/3/14
 * Time: 10:10 PM
 */
class PageContentLibraryImpl implements PageContentLibrary
{

    /** @var Page */
    private $page;
    /** @var  DB */
    private $db;
    private $listContentPreparedStatement;
    private $searchLibraryPreparedStatement;
    private $idArray;


    function __construct(DB $db, Page $page)
    {
        $this->db = $db;
        $this->page = $page;
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
        return array_filter($this->idArray, function (PageContentImpl $content) use ($time) {
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
            $this->idArray[$id] = new PageContentImpl($this->db, $this->page, $id);

    }

    private function setUpList()
    {

        if ($this->listContentPreparedStatement == null) {
            $this->listContentPreparedStatement =
                $this->db->getConnection()->prepare("SELECT DISTINCT id FROM PageContent WHERE page_id = ?");
            $this->listContentPreparedStatement->execute(array($this->page->getID()));
            foreach ($this->listContentPreparedStatement->fetchAll(PDO::FETCH_ASSOC) as $val) {
                $this->idArray[$val["id"]] = new PageContentImpl($this->db, $this->page, $val["id"]);
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
                prepare("SELECT DISTINCT id FROM PageContent WHERE page_id = ? AND content LIKE ? AND time >= ? ");
        }
        $this->searchLibraryPreparedStatement->execute(array($this->page->getID(), "%$string%",
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

    /**
     * Returns the page instance of which the library is registered.
     * @return Page
     */
    public function getPage()
    {
        return $this->page;
    }

    /**
     * @return TypeHandler
     */
    public function generateTypeHandler()
    {
        // TODO: Implement generateTypeHandler() method.
    }
}