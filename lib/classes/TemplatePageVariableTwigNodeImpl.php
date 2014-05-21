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
     * @param string $page_id
     */
    function __construct($line, $tag, $id, $page_id = "")
    {
        parent::__construct(array(), array('id'=>$id, 'page_id'=> $page_id), $line, $tag);

    }

    public function compile(Twig_Compiler $compiler)
    {


        if($this->getAttribute("page_id") != ""){
            $p = uniqid("variable");
            $compiler->write("echo (\$$p = \$context['page_order']->getPage('{$this->getAttribute('page_id')}')) == null? '':\${$p}->getVariables()->getValue('{$this->getAttribute('id')}');")->raw("\n");
        } else {
            $compiler->write("echo \$context['current_page']->getVariables()->getValue('{$this->getAttribute('id')}');")->raw("\n");
        }


    }

} 