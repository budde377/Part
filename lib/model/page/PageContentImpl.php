<?php
namespace ChristianBudde\Part\model\page;

use ChristianBudde\Part\BackendSingletonContainer;
use ChristianBudde\Part\controller\ajax\type_handler\TypeHandler;
use ChristianBudde\Part\controller\json\PageContentObjectImpl;
use ChristianBudde\Part\model\ContentImpl;


/**
 * Created by JetBrains PhpStorm.
 * User: budde
 * Date: 25/05/13
 * Time: 22:45
 * To change this template use File | Settings | File Templates.
 */
class PageContentImpl extends ContentImpl implements PageContent, \Serializable
{

    private $page;
    private $page_id;
    private $container;
    private $content_id;

    public function __construct(BackendSingletonContainer $container, Page $page, $content_id = "")
    {
        $this->container = $container;
        $this->page = $page;
        $this->page_id = $page->getID();
        $this->content_id = $content_id;
        $this->setup();

    }

    public function addContent($content)
    {
        if(!$this->page->exists()){
            return null;
        }
        $this->updatePageId();
        return parent::addContent($content);
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
     * @return TypeHandler
     */
    public function generateTypeHandler()
    {
        return $this->container->getTypeHandlerLibraryInstance()->getPageContentTypeHandlerInstance($this);
    }

    private function updatePageId()
    {
        $this->page_id = $this->page->getID();
    }

    public function latestContent()
    {
        $this->updatePageId();
        return parent::latestContent();
    }

    public function latestTime()
    {
        $this->updatePageId();
        return parent::latestTime();
    }

    public function listContentHistory($from = null, $to = null, $onlyTimestamps = false)
    {
        $this->updatePageId();
        return parent::listContentHistory($from, $to, $onlyTimestamps);
    }

    public function getContentAt($time)
    {
        $this->updatePageId();
        return parent::getContentAt($time);
    }

    public function containsSubString($string, $fromTime = null)
    {
        $this->updatePageId();
        return parent::containsSubString($string, $fromTime);
    }

    public function getId()
    {
        $this->updatePageId();
        return parent::getId();
    }

    public function jsonSerialize()
    {
        $this->updatePageId();
        return parent::jsonSerialize();
    }

    private function setup()
    {
        $connection = $this->container->getDBInstance()->getConnection();
        $addContentStm = $connection->prepare("
          INSERT INTO PageContent (id,page_id, `time`, content)
          VALUES (:id,:page_id, FROM_UNIXTIME(:time), :content)");
        $addContentStm->bindParam(":page_id", $this->page_id);

        $latestContentStm = $connection->prepare("
          SELECT content, UNIX_TIMESTAMP(time) AS time
          FROM PageContent
          WHERE id=:id AND page_id = :page_id
          ORDER BY time DESC
          LIMIT 1");
        $latestContentStm->bindParam(":page_id", $this->page_id);

        $listContentStm = $connection->prepare("
          SELECT UNIX_TIMESTAMP(time) AS time
          FROM PageContent
          WHERE page_id = :page_id AND id=:id AND :from <= UNIX_TIMESTAMP(time) AND UNIX_TIMESTAMP(time) <= :to
        ");
        $listContentStm->bindParam(":page_id", $this->page_id);

        $listContentContentStm = $connection->prepare("
          SELECT content,UNIX_TIMESTAMP(time) as time
          FROM PageContent
          WHERE page_id = :page_id AND  id=:id AND :from <= UNIX_TIMESTAMP(time) AND UNIX_TIMESTAMP(time) <= :to");
        $listContentContentStm->bindParam(":page_id", $this->page_id);

        $getContentAtStm = $connection->prepare("
          SELECT content,UNIX_TIMESTAMP(time) as time
          FROM PageContent
          WHERE id=:id AND page_id = :page_id AND time <= FROM_UNIXTIME(:time)
          ORDER BY time DESC
          LIMIT 1");
        $getContentAtStm->bindParam(":page_id", $this->page_id);

        $containSubStrStm = $connection->prepare("
          SELECT time
          FROM PageContent
          WHERE content LIKE :like AND id = :id AND page_id= :page_id AND time >= FROM_UNIXTIME(:time)");
        $containSubStrStm->bindParam(":page_id", $this->page_id);

        parent::__construct(
            $this->content_id,
            function () {
                return $this->page->modify();
            },
            $addContentStm,
            $latestContentStm,
            $listContentStm,
            $listContentContentStm,
            $getContentAtStm,
            $containSubStrStm);
    }


    /**
     * (PHP 5 &gt;= 5.1.0)<br/>
     * String representation of object
     * @link http://php.net/manual/en/serializable.serialize.php
     * @return string the string representation of the object or null
     */
    public function serialize()
    {
        return serialize([$this->container, $this->page, $this->page_id, $this->content_id]);
    }

    /**
     * (PHP 5 &gt;= 5.1.0)<br/>
     * Constructs the object
     * @link http://php.net/manual/en/serializable.unserialize.php
     * @param string $serialized <p>
     * The string representation of the object.
     * </p>
     * @return void
     */
    public function unserialize($serialized)
    {
        $array = unserialize($serialized);
        $this->container = $array[0];
        $this->page = $array[1];
        $this->page_id = $array[2];
        $this->content_id = $array[3];
        $this->setup();
    }
}