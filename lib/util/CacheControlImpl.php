<?php
namespace ChristianBudde\Part\util;
use ChristianBudde\Part\model\page\CurrentPageStrategy;
use ChristianBudde\Part\model\page\Page;
use ChristianBudde\Part\model\site\Site;

/**
 * User: budde
 * Date: 9/8/13
 * Time: 10:24 PM
 */

class CacheControlImpl implements CacheControl{

    /**
     * @var bool
     */
    private $enabledCacheControl = true;
    private $hasBeenSetUp = false;
    /** @var  Page */
    private $page;
    /** @var  Site */
    private $site;

    public function __construct(Site $site, CurrentPageStrategy $currentPageStrategy){
        $this->page = $currentPageStrategy->getCurrentPage();
        $this->site = $site;
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
        $t = max($this->page->lastModified(), $this->site->lastModified());
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