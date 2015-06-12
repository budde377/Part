<?php
namespace ChristianBudde\Part\view\template;
/**
 * Created by PhpStorm.
 * User: budde
 * Date: 10/23/13
 * Time: 7:57 PM
 */
use Twig_Compiler;
use Twig_Node;

class SiteContentTwigNodeImpl extends Twig_Node{


    /**
     * @param int $line
     * @param int $tag
     * @param \Twig_Node_Expression $id
     */
    function __construct( $line, $tag, \Twig_Node_Expression $id = null)
    {
        parent::__construct(array('id'=>$id), array(), $line, $tag);
    }

    public function compile(Twig_Compiler $compiler)
    {
        if($this->getNode('id') == null){
            $compiler->write("echo \$context['site']->getContent('')->latestContent();")->raw("\n");
        } else {
            $compiler->write("echo \$context['site']->getContent(")->subcompile($this->getNode('id'))->write(")->latestContent();")->raw("\n");
        }

    }


}