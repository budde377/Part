<?php
namespace ChristianBudde\Part\model\site;
use ChristianBudde\Part\BackendSingletonContainer;
use ChristianBudde\Part\controller\ajax\type_handler\TypeHandler;
use ChristianBudde\Part\model\ContentLibraryImpl;


/**
 * Created by PhpStorm.
 * User: budde
 * Date: 3/3/14
 * Time: 10:53 PM
 */
class SiteContentLibraryImpl extends ContentLibraryImpl implements SiteContentLibrary
{


    private $container;


    function __construct(BackendSingletonContainer $container)
    {
        $this->container = $container;

        $connection = $container->getDBInstance()->getConnection();

        $listContentStm = $connection->prepare("SELECT DISTINCT id FROM SiteContent");
        $searchLibStm = $connection->prepare("
          SELECT DISTINCT id
          FROM SiteContent
          WHERE content LIKE :like AND time >= FROM_UNIXTIME(:time) ");

        parent::__construct($listContentStm, $searchLibStm, function($id){
            return new SiteContentImpl($this->container, $id);
        });
    }


    /**
     * @return TypeHandler
     */
    public function generateTypeHandler()
    {
        return $this->container->getTypeHandlerLibraryInstance()->getSiteContentLibraryTypeHandlerInstance($this);
    }
}