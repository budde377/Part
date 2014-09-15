<?php
namespace ChristianBudde\cbweb\view\template;
/**
 * Created by PhpStorm.
 * User: budde
 * Date: 10/23/13
 * Time: 7:57 PM
 */
use \Twig_Node;
use \Twig_Compiler;

class TemplatePageContentTwigNodeImpl extends Twig_Node{


    /**
     * @param int $line
     * @param int $tag
     * @param string $id
     * @param string $page_id
     */
    function __construct($line, $tag, $page_id = "", $id = "")
    {
        parent::__construct(array(), array('page_id'=> $page_id, 'id'=>$id), $line, $tag);

    }

    public function compile(Twig_Compiler $compiler)
    {

        if($this->getAttribute("page_id") != ""){
            $p = uniqid("variable");
            $compiler->write("echo (\$$p = \$context['page_order']->getPage('{$this->getAttribute('page_id')}')) == null? '':\${$p}->getContent('{$this->getAttribute('id')}')->latestContent();")->raw("\n");
        } else {
            $compiler->write("echo \$context['current_page']->getContent('{$this->getAttribute('id')}')->latestContent();")->raw("\n");
        }
    }


}