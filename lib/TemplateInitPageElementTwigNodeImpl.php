<?php
namespace ChristianBudde\cbweb;
use \Twig_Node;
use \Twig_Compiler;
/**
 * Created by PhpStorm.
 * User: budde
 * Date: 10/23/13
 * Time: 9:14 PM
 */

class TemplateInitPageElementTwigNodeImpl extends Twig_Node
{


    /**
     * @param array $name
     * @param int $line
     * @param null|string $tag
     */
    public function __construct($name, $line, $tag)
    {

        parent::__construct(array(), array('name'=>$name), $line, $tag);
    }

    public function compile(Twig_Compiler $compiler)
    {
        $name = $this->getAttribute('name');
        $varName = uniqid("var");
        $compiler->write("\$$varName = \$context['page_element_factory']->getPageElement('$name'); ")->raw("\n")
                 ->write("if(\$$varName == null)")->raw("\n")
                 ->write("throw new Twig_Error_Runtime('Could not find page element: \"$name\"', {$this->getLine()});")->raw("\n")
                 ->write(" else if(!\${$varName}->hasBeenSetUp())")->raw("\n")
                 ->write("\${$varName}->setUpElement();")->raw("\n");
    }

} 