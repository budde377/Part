<?php
namespace ChristianBudde\Part\view\page_element;

/**
 * User: budde
 * Date: 5/29/12
 * Time: 10:56 AM
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
