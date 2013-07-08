<?php
require_once dirname(__FILE__) . '/../_interface/PageContent.php';
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
    private $history;

    /** @var  PDOStatement */
    private $preparedAddStatement;


    public function __construct(DB $database, Page $page, $id = null)
    {
        $this->db = $database;
        $this->page = $page;
        $this->id = $id;
    }

    /**
     * @param int $from List history from a specific time. If null the whole history will be returned.
     * @return array An array containing arrays with keys: "time" and "content"
     */
    public function listContentHistory($from = null)
    {
        $this->initializeHistory();
        $result = $this->history;
        if($from != null){
            $result = array();
            foreach($this->history as $e){
                if($e['time'] >= $from){
                    $result[] = $e;
                }
            }
        }
        return $result;
    }

    /**
     * @return string | null Returns the latest content as a string or null if no content exists.
     */
    public function latestContent()
    {
        if ($this->content == null && $this->history == null && $this->content == null) {
            if ($this->id == null) {
                $prep = $this->db->getConnection()->prepare("SELECT content FROM PageContent WHERE id is NULL AND page_id=? ORDER BY time DESC LIMIT 1");
                $prep->execute(array($this->page->getID()));
            } else {
                $prep = $this->db->getConnection()->prepare("SELECT content FROM PageContent WHERE id=? AND page_id = ? ORDER BY time DESC LIMIT 1");
                $prep->execute(array($this->id, $this->page->getID()));
            }
            $this->content = $prep->fetch(PDO::FETCH_ASSOC);
            $this->content = $this->content['content'];
        } else if($this->content == null && $this->history != null){
            $this->content = $this->history[count($this->history)-1]['content'];
        }

        return $this->content;
    }

    /**
     * @param string $content Adds new content. This will be the latest upon addition.
     * @return void
     */
    public function addContent($content)
    {
        $this->initializeHistory();
        if ($this->preparedAddStatement == null) {
            $this->preparedAddStatement = $this->db->getConnection()->prepare("
            INSERT INTO PageContent (id,page_id,time, content)
            VALUES (?, ?, ?, ?)");
        }
        $t = time();
        $this->preparedAddStatement->execute(array($this->id, $this->page->getID(), date("Y-m-d H:i:s",$t), $content));
        $this->content = $content;
        $this->history[] = array('content' => $content, 'time' => $t);
    }

    private function initializeHistory()
    {
        if ($this->history != null) {
            return;
        }
        if ($this->id == null) {
            $prep = $this->db->getConnection()->prepare("SELECT content, UNIX_TIMESTAMP(time) as time FROM PageContent WHERE id is NULL AND page_id = ? ORDER BY time ASC");
            $prep->execute(array($this->page->getID()));
        } else {
            $prep = $this->db->getConnection()->prepare("SELECT content, UNIX_TIMESTAMP(time) as time FROM PageContent WHERE id=? AND page_id = ? ORDER BY time ASC");
            $prep->execute(array($this->id, $this->page->getID()));
        }
        $this->history = $prep->fetchAll(PDO::FETCH_ASSOC);

    }
}