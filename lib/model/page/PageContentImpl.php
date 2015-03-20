<?php
namespace ChristianBudde\Part\model\page;

use ChristianBudde\Part\BackendSingletonContainer;
use ChristianBudde\Part\controller\ajax\type_handler\TypeHandler;
use ChristianBudde\Part\controller\json\PageContentObjectImpl;
use ChristianBudde\Part\model\ContentImpl;
use ChristianBudde\Part\util\Observable;
use ChristianBudde\Part\util\Observer;


/**
 * Created by JetBrains PhpStorm.
 * User: budde
 * Date: 25/05/13
 * Time: 22:45
 * To change this template use File | Settings | File Templates.
 */
class PageContentImpl extends ContentImpl implements PageContent, Observer
{

    private $page;
    private $page_id;


    public function __construct(BackendSingletonContainer $container, Page $page, $id = "")
    {
        $connection = $container->getDBInstance()->getConnection();
        $this->container = $container;
        $this->page = $page;
        $page->attachObserver($this);
        $this->page_id = $page->getID();

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
            $id,
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

    public function addContent($content)
    {
        if(!$this->page->exists()){
            return null;
        }
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

    public function onChange(Observable $subject, $changeType)
    {
        $this->page_id = $this->page->getID();
    }
}