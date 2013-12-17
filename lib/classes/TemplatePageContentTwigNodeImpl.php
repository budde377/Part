<?php
/**
 * Created by PhpStorm.
 * User: budde
 * Date: 10/23/13
 * Time: 7:57 PM
 */

class TemplatePageContentTwigNodeImpl extends Twig_Node{


    /**
     * @param int $line
     * @param int $tag
     * @param string $id
     */
    function __construct($line, $tag, $id = "")
    {
        parent::__construct(array(), array('id'=>$id), $line, $tag);

    }

    public function compile(Twig_Compiler $compiler)
    {
        $compiler->write("echo \$context['current_page']->getContent('{$this->getAttribute('id')}')->latestContent();")->raw("\n");

    }


}