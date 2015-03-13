<?php
namespace ChristianBudde\Part\model\page;

use ChristianBudde\Part\BackendSingletonContainer;
use ChristianBudde\Part\controller\ajax\type_handler\TypeHandler;
use ChristianBudde\Part\model\ContentLibraryImpl;
use ChristianBudde\Part\util\Observable;
use ChristianBudde\Part\util\Observer;

/**
 * Created by PhpStorm.
 * User: budde
 * Date: 3/3/14
 * Time: 10:10 PM
 */
class PageContentLibraryImpl extends ContentLibraryImpl implements PageContentLibrary, Observer
{
    private $page;
    private $page_id;


    function __construct(BackendSingletonContainer $container, Page $page)
    {
        $this->container = $container;
        $this->page = $page;
        $page->attachObserver($this);
        $this->page_id = $page->getID();

        $connection = $container->getDBInstance()->getConnection();

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

    public function onChange(Observable $subject, $changeType)
    {
        $this->page_id = $this->page->getID();
    }
}