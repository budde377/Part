<?php
/**
 * Created by PhpStorm.
 * User: budde
 * Date: 1/31/14
 * Time: 11:10 PM
 */

class TemplatePageVariableTwigNodeImpl extends Twig_Node{
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
        $compiler->write("echo \$context['current_page']->getVariables()->getValue('{$this->getAttribute('id')}');")->raw("\n");

    }

} 