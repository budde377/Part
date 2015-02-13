<?php
namespace ChristianBudde\Part\controller\json;

use ChristianBudde\Part\model\page\Page;
use ChristianBudde\Part\model\page\PageOrder;

/**
 * Created by PhpStorm.
 * User: budde
 * Date: 9/4/14
 * Time: 12:47 PM
 */

class PageOrderObjectImpl extends ObjectImpl
{

    function __construct(PageOrder $order)
    {
        parent::__construct("page_order");
        $this->setVariable('order', $this->orderBuilder($order));
        $this->setVariable('inactive', $order->listPages(PageOrder::LIST_INACTIVE));
    }


    private function orderBuilder(PageOrder $order){

        $resultArray = [];

        $resultArray[null] = $order->getPageOrder();

        foreach($order->listPages(PageOrder::LIST_ACTIVE) as $page){
            /** @var $page Page */
            if(count($o = $order->getPageOrder($page))>0){
                $resultArray[$page->getID()] = $o;
            }
        }


        return $resultArray;


    }
}