<?php
namespace ChristianBudde\cbweb\controller\json;



/**
 * Created by PhpStorm.
 * User: budde
 * Date: 8/30/14
 * Time: 9:08 PM
 */
class CompositeFunctionImpl extends ProgramImpl implements CompositeFunction
{


    private $functions = [];

    /**
     * @param Target $target
     * @param JSONFunction[] $functions
     */
    function __construct(Target $target, array $functions = [])
    {
        parent::__construct($target);
        $this->functions = $functions;
    }


    /**
     * @return array
     */
    public function listFunctions()
    {
        return $this->functions;
    }

    /**
     * @return array
     */
    public function getAsArray()
    {
        return ['type'=>'composite_function', 'id' => $this->id, 'functions' => $this->functions, 'target'=>$this->target];
    }


}