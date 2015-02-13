<?php
namespace ChristianBudde\Part\test\stub;
use ChristianBudde\Part\view\page_element\PageElementFactory;

/**
 * Created by JetBrains PhpStorm.
 * User: budde
 * Date: 5/29/12
 * Time: 10:56 AM
 * To change this template use File | Settings | File Templates.
 */
class NullPageElementFactoryImpl implements PageElementFactory
{


    /**
     * {@inheritdoc}
     */
    public function getPageElement($name, $cached = true)
    {
        return null;
    }

    /**
     * Will clear cache
     * @return void
     */
    public function clearCache()
    {
        return null;
    }
}
