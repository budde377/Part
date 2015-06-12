<?php
namespace ChristianBudde\Part\view\template;

use Twig_Compiler;
use Twig_Node;

/**
 * Created by PhpStorm.
 * User: budde
 * Date: 10/23/13
 * Time: 7:57 PM
 */
class PageElementTwigNodeImpl extends Twig_Node
{

    /**
     * @param \Twig_Node_Expression $nameArray
     * @param int $line
     * @param int $tag
     */
    function __construct(\Twig_Node_Expression $nameArray, $line, $tag)
    {
        parent::__construct(array('name' => $nameArray), array(), $line, $tag);

    }

    public function compile(Twig_Compiler $compiler)
    {

        $nameVar = $compiler->getVarName();
        $pageElementVar = $compiler->getVarName();

        $compiler->write("\$$nameVar = ")->subcompile($this->getNode('name'))->write(";")->raw("\n");
        $compiler->write("\$$pageElementVar = \$context['page_element_factory']->getPageElement(\$$nameVar); ")->raw("\n")
            ->write("if(\$$pageElementVar == null){")->raw("\n")
            ->write("throw new Twig_Error_Runtime('Could not find page element: \"'.addslashes(\$$nameVar).'\"', {$this->getLine()});")->raw("\n")
            ->write("} else if(!\${$pageElementVar}->hasBeenSetUp()){")->raw("\n")
            ->write("\${$pageElementVar}->setUpElement();")->raw("\n")
            ->write("echo \${$pageElementVar}->generateContent();")->raw("\n");
        $compiler->write("}");

    }


}