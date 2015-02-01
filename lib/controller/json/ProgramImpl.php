<?php
namespace ChristianBudde\cbweb\controller\json;


/**
 * Created by PhpStorm.
 * User: budde
 * Date: 8/31/14
 * Time: 4:12 PM
 */

abstract class ProgramImpl extends ElementImpl implements Program{
    protected $id;
    protected $target;

    function __construct(Target $target)
    {
        $this->target = $target;
    }


    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = intval($id);
    }


    /**
     * @return Target
     */
    public function getTarget()
    {
        return $this->target;
    }


} 