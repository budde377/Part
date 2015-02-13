<?php
namespace ChristianBudde\Part\model\page;
use Iterator;

/**
 * Created by JetBrains PhpStorm.
 * User: budde
 * Date: 17/01/13
 * Time: 22:36
 */
interface DefaultPageLibrary extends Iterator
{
    /**
     * Will list all default pages, as defined in given config.
     * @return array An array containing instances of Page representing the Default pages
     */
    public function listPages();

    /**
     * Will return a default page given an ID
     * @param string $id The id
     * @return Page | null Instance matching the ID or NULL on no such page
     */
    public function getPage($id);


}
