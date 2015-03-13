<?php
/**
 * Created by PhpStorm.
 * User: budde
 * Date: 3/13/15
 * Time: 11:52 AM
 */

namespace ChristianBudde\Part\model;


use PDO;
use PDOStatement;

abstract class ContentLibraryImpl implements ContentLibrary
{

    private $list_content_stm;
    private $idArray = [];
    private $search_library_stm;
    private $content_constructor;

    function __construct(PDOStatement $list_content_stm, PDOStatement $search_lib_stm, callable $content_constructor)
    {
        $this->list_content_stm = $list_content_stm;
        $this->search_library_stm = $search_lib_stm;
        $this->content_constructor = $content_constructor;
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
        return array_filter($this->idArray, function (Content $content) use ($time) {
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
            $this->idArray[$id] = $this->createContent($id);

    }

    private function setUpList()
    {

        if ($this->list_content_stm == null) {
            return;
        }

        $this->list_content_stm->execute();
        foreach ($this->list_content_stm->fetchAll(PDO::FETCH_ASSOC) as $val) {
            $this->idArray[$val["id"]] = $this->createContent($val["id"]);
        }
        $this->list_content_stm = null;

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
        $this->search_library_stm->bindValue(':like', "%$string%");
        $this->search_library_stm->bindValue(':time', $time == null ? 0 : $time);
        $this->search_library_stm->execute();

        $retArray = [];
        foreach ($this->search_library_stm->fetchAll(PDO::FETCH_ASSOC) as $val) {
            $id = $val["id"];
            if (!isset($this->idArray[$id])) {
                continue;
            }
            $retArray[$id] = $this->idArray[$id];
        }

        return $retArray;
    }

    private function createContent($id)
    {

        $constructor = $this->content_constructor;
        return $constructor($id);

    }
}