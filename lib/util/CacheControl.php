<?php
namespace ChristianBudde\Part\util;

/**
 * User: budde
 * Date: 9/8/13
 * Time: 10:21 PM
 */

interface CacheControl {

    /**
     * Will disable cache
     * @return void
     */
    public function disableCache();

    /**
     * Returns true if the cache is enabled
     * @return bool
     */
    public function isEnabled();


    /**
     * Will setup the cache, if enabled
     * @return bool true if cache was setup, else false.
     */
    public function setUpCache();


}