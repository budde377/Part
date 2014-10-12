<?php
namespace ChristianBudde\cbweb\view\template;
/**
 * Created by PhpStorm.
 * User: budde
 * Date: 1/31/14
 * Time: 11:17 PM
 */
use \Twig_Node;
use \Twig_Compiler;

class SiteVariableTwigNodeImpl extends Twig_Node{
    /**
     * @param int $line
     * @param int $tag
     * @param string $id
     */
    function __construct($line, $tag, $id)
    {
        parent::__construct(array(), array('id'=>$id), $line, $tag);

    }

    public function compile(Twig_Compiler $compiler)
    {
        $compiler->write("echo \$context['site']->getVariables()->getValue('{$this->getAttribute('id')}');")->raw("\n");

    }
} 