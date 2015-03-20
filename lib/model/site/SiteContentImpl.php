<?php
namespace ChristianBudde\Part\model\site;

use ChristianBudde\Part\BackendSingletonContainer;
use ChristianBudde\Part\controller\ajax\type_handler\TypeHandler;
use ChristianBudde\Part\controller\json\SiteContentObjectImpl;
use ChristianBudde\Part\model\ContentImpl;


/**
 * Created by PhpStorm.
 * User: budde
 * Date: 10/23/13
 * Time: 12:54 PM
 */
class SiteContentImpl extends ContentImpl implements SiteContent
{

    private $container;


    public function __construct(BackendSingletonContainer $container, $id = "")
    {
        $connection = $container->getDBInstance()->getConnection();
        $this->container = $container;
        parent::__construct(
            $id,
            function () {
                return $this->container->getSiteInstance()->modify();
            },
            $connection->prepare("
              INSERT INTO SiteContent (id, `time`, content)
              VALUES (:id, FROM_UNIXTIME(:time), :content)"),
            $connection->prepare("
              SELECT content, UNIX_TIMESTAMP(time) AS time
              FROM SiteContent
              WHERE id=:id ORDER BY time DESC LIMIT 1"),
            $connection->prepare("
              SELECT UNIX_TIMESTAMP(time) as time
              FROM SiteContent
              WHERE id=:id AND :from <= UNIX_TIMESTAMP(time) AND UNIX_TIMESTAMP(time) <= :to"),
            $connection->prepare("
              SELECT content,UNIX_TIMESTAMP(time) as time
              FROM SiteContent
              WHERE id=:id AND :from <= UNIX_TIMESTAMP(time) AND UNIX_TIMESTAMP(time) <= :to"),
            $connection->prepare("
              SELECT content,UNIX_TIMESTAMP(time) as time
              FROM SiteContent
              WHERE id=:id AND time <= FROM_UNIXTIME(:time)
              ORDER BY time DESC
              LIMIT 1"),
            $connection->prepare("
              SELECT time
              FROM SiteContent
              WHERE content LIKE :like AND id = :id AND time >= FROM_UNIXTIME(:time)"));

    }


    /**
     * Serializes the object to an instance of JSONObject.
     * @return Object
     */
    public function jsonObjectSerialize()
    {
        return new SiteContentObjectImpl($this);
    }

    /**
     * @return TypeHandler
     */
    public function generateTypeHandler()
    {
        return $this->container->getTypeHandlerLibraryInstance()->getSiteContentTypeHandlerInstance($this);
    }
}