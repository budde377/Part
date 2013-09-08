<?php
require_once dirname(__FILE__).'/../_interface/CacheControl.php';

/**
 * Created by JetBrains PhpStorm.
 * User: budde
 * Date: 9/8/13
 * Time: 10:24 PM
 * To change this template use File | Settings | File Templates.
 */

class CacheControlImpl implements CacheControl{

    /**
     * @var bool
     */
    private $enabledCacheControl = true;
    private $hasBeenSetUp = false;
    /** @var  Page */
    private $page;

    public function __construct(CurrentPageStrategy $currentPageStrategy){
        $this->page = $currentPageStrategy->getCurrentPage();
    }

    /**
     * Will disable cache
     * @return void
     */
    public function disableCache()
    {
        if($this->hasBeenSetUp){
            return;
        }
        $this->enabledCacheControl = false;
    }

    /**
     * Returns true if the cache is enabled
     * @return bool
     */
    public function isEnabled()
    {
        return $this->enabledCacheControl;
    }


    public function setUpCache(){
        if(!$this->enabledCacheControl){
            return false;
        }
        $t = $this->page->lastModified();
        $this->hasBeenSetUp = true;
        header("Cache-Control: must-revalidate");
        header_remove('Pragma');
        header_remove('Expires');
        header("Last-Modified: ".gmdate("D, d M Y H:i:s", $t)." GMT");
        if(!isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) || empty($_SERVER['HTTP_IF_MODIFIED_SINCE']) ||
            @strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']) != $t){
            return false;
        }
        header("HTTP/1.1 304 Not Modified");
        return true;
    }

}