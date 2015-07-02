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
class SiteContentLibraryImpl extends ContentLibraryImpl implements SiteContentLibrary,\Serializable
{


    private $container;


    function __construct(BackendSingletonContainer $container)
    {
        $this->container = $container;
        $this->setup();
    }


    /**
     * @return TypeHandler
     */
    public function generateTypeHandler()
    {
        return $this->container->getTypeHandlerLibraryInstance()->getSiteContentLibraryTypeHandlerInstance($this);
    }

    private function setup()
    {
        $connection = $this->container->getDBInstance()->getConnection();

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
     * (PHP 5 &gt;= 5.1.0)<br/>
     * String representation of object
     * @link http://php.net/manual/en/serializable.serialize.php
     * @return string the string representation of the object or null
     */
    public function serialize()
    {
        return serialize($this->container);
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
        $this->container = unserialize($serialized);
        $this->setup();
    }
}