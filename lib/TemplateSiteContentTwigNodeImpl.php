<?php
namespace ChristianBudde\cbweb;
/**
 * Created by PhpStorm.
 * User: budde
 * Date: 10/23/13
 * Time: 7:57 PM
 */
use \Twig_Node;
use \Twig_Compiler;

class TemplateSiteContentTwigNodeImpl extends Twig_Node{


    /**
     * @param int $line
     * @param int $tag
     * @param null $id
     */
    function __construct( $line, $tag, $id = null)
    {
        parent::__construct(array(), array('id'=>$id), $line, $tag);
    }

    public function compile(Twig_Compiler $compiler)
    {
        $compiler->write("echo \$context['site']->getContent('{$this->getAttribute('id')}')->latestContent();")->raw("\n");
    }


}