<?php
namespace ChristianBudde\Part\model\page;

use ChristianBudde\Part\BackendSingletonContainer;
use ChristianBudde\Part\controller\ajax\type_handler\TypeHandler;
use ChristianBudde\Part\model\ContentLibraryImpl;

/**
 * Created by PhpStorm.
 * User: budde
 * Date: 3/3/14
 * Time: 10:10 PM
 */
class PageContentLibraryImpl extends ContentLibraryImpl implements PageContentLibrary, \Serializable
{
    private $page;
    private $page_id;


    function __construct(BackendSingletonContainer $container, Page $page)
    {
        $this->container = $container;
        $this->page = $page;
        $this->page_id = $page->getID();

        $this->setup();
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
        return $this->container->getTypeHandlerLibraryInstance()->getPageContentLibraryTypeHandlerInstance($this);
    }

    public function listContents($time = 0)
    {
        $this->updatePageId();
        return parent::listContents($time);
    }

    public function searchLibrary($string, $time = null)
    {
        $this->updatePageId();
        return parent::searchLibrary($string, $time);
    }

    private function updatePageId()
    {
        $this->page_id = $this->page->getID();
    }


    /**
     * (PHP 5 &gt;= 5.1.0)<br/>
     * String representation of object
     * @link http://php.net/manual/en/serializable.serialize.php
     * @return string the string representation of the object or null
     */
    public function serialize()
    {
        return serialize([$this->page, $this->page_id, $this->container]);
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
        $this->page = $array[0];
        $this->page_id = $array[1];
        $this->container = $array[2];
        $this->setup();
    }

    private function setup()
    {
        $connection = $this->container->getDBInstance()->getConnection();

        $listContentStm = $connection->prepare("
          SELECT DISTINCT id
          FROM PageContent
          WHERE page_id = :page_id");
        $listContentStm->bindParam(":page_id", $this->page_id);

        $searchLibStm = $connection->prepare("
          SELECT DISTINCT id
          FROM PageContent
          WHERE page_id = :page_id AND content LIKE :like AND time >= FROM_UNIXTIME(:time)");
        $searchLibStm->bindParam(":page_id", $this->page_id);

        parent::__construct($listContentStm, $searchLibStm, function ($id) {
            return new PageContentImpl($this->container, $this->page, $id);
        });
    }
}