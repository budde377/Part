<?php
namespace ChristianBudde\cbweb\controller\json;


/**
 * Created by PhpStorm.
 * User: budde
 * Date: 8/31/14
 * Time: 4:12 PM
 */

abstract class JSONProgramImpl extends JSONElementImpl implements JSONProgram{
    protected $id;
    protected $target;

    function __construct(JSONTarget $target)
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
     * @param JSONTarget $target
     * @return void
     */
    public function setTarget(JSONTarget $target)
    {
        $this->target = $target;
    }


    /**
     * @return string | JSONFunction
     */
    public function getTarget()
    {
        return $this->target;
    }


} 