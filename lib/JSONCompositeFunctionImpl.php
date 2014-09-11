<?php
namespace ChristianBudde\cbweb;
/**
 * Created by PhpStorm.
 * User: budde
 * Date: 8/30/14
 * Time: 9:08 PM
 */
class JSONCompositeFunctionImpl extends JSONProgramImpl implements JSONCompositeFunction
{




    private $functions = array();

    function __construct(JSONTarget $target)
    {
        parent::__construct($target);
    }


    /**
     * @return array
     */
    public function listFunctions()
    {
        return $this->functions;
    }

    public function appendFunction(JSONFunction $function)
    {
        $function->setRootTarget($this->target);
        $this->functions[] = $function;
    }

    public function removeFunction(JSONFunction $function)
    {
        $newArray = array();
        foreach ($this->functions as $func) {
            if ($function === $func) {
                continue;
            }
            $newArray[] = $func;
        }
        $this->functions = $newArray;
    }

    public function prependFunction(JSONFunction $function)
    {
        $function->setRootTarget($this->target);
        $this->functions = array_merge([$function], $this->functions);
    }
    /**
     * @return array
     */
    public function getAsArray()
    {
        return ['type'=>'composite_function', 'id' => $this->id, 'functions' => $this->functions, 'target'=>$this->target];
    }

    public function setTarget(JSONTarget $target){
        foreach($this->functions as $func){
            /** @var JSONFunction  $func */
            $func->setRootTarget($target);
        }
        parent::setTarget($target);
    }
}