<?php
namespace ChristianBudde\cbweb\controller\json;


/**
 * Created by PhpStorm.
 * User: budde
 * Date: 8/31/14
 * Time: 4:02 PM
 */

interface Program extends Element{

    /**
     * @return Target
     */
    public function getTarget();

    /**
     * @return int
     */
    public function getId();

    /**
     * @param int $id
     * @return mixed
     */
    public function setId($id);

} 