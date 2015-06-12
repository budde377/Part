<?php
namespace ChristianBudde\Part\view\template;
/**
 * Created by PhpStorm.
 * User: budde
 * Date: 1/31/14
 * Time: 11:17 PM
 */
use Twig_Compiler;
use Twig_Node;

class SiteVariableTwigNodeImpl extends Twig_Node{
    /**
     * @param int $line
     * @param int $tag
     * @param \Twig_Node_Expression $id
     */
    function __construct($line, $tag, \Twig_Node_Expression $id)
    {
        parent::__construct(array('id'=>$id), [], $line, $tag);

    }

    public function compile(Twig_Compiler $compiler)
    {
        if($this->getNode('id') == null){
            $compiler->write("echo \$context['site']->getVariables()->getValue('');")->raw("\n");
        } else {
            $compiler->write("echo \$context['site']->getVariables()->getValue(")->subcompile($this->getNode('id'))->write(");")->raw("\n");
        }
    }
} 