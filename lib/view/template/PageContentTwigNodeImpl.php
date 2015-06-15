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

class PageContentTwigNodeImpl extends Twig_Node
{


    /**
     * @param int $line
     * @param int $tag
     * @param \Twig_Node_Expression $id
     */
    function __construct($line, $tag, \Twig_Node_Expression $id = null)
    {
        parent::__construct(array('id' => $id), array(), $line, $tag);

    }

    public function compile(Twig_Compiler $compiler)
    {

        if ($this->getNode('id') == null) {
            $compiler->write("echo \$context['current_page']->getContent('');");
            return;
        }
        $inputVarName = $compiler->getVarName();
        $idVarName = $compiler->getVarName();
        $pageIdVarName = $compiler->getVarName();

        $compiler->write("\$$inputVarName = ")->subcompile($this->getNode('id'))->write(';')->raw("\n");
        $compiler->write("if(is_array(\$$inputVarName) && count(\$$inputVarName) == 2){")->raw("\n");
        $compiler->write("\$$idVarName = array_pop(\$$inputVarName);")->raw("\n");
        $compiler->write("\$$pageIdVarName = array_pop(\$$inputVarName);")->raw("\n");
        $compiler->write("if(is_scalar(\$$pageIdVarName)){")->raw("\n");
        $compiler->write("\$$pageIdVarName = \$context['page_order']->getPage(\$$pageIdVarName);")->raw("\n");
        $compiler->write("}")->raw("\n");

        $compiler->write("if(\$$pageIdVarName instanceof \\ChristianBudde\\Part\\model\\page\\Page) {");
        $compiler->write("echo \${$pageIdVarName}->getContent(\$$idVarName);")->raw("\n");
        $compiler->write("} ")->raw("\n");
        $compiler->write("} else if(is_scalar(\$$inputVarName)){")->raw("\n");
        $compiler->write("echo \$context['current_page']->getContent(\$$inputVarName);")->raw("\n");
        $compiler->write("}")->raw("\n");


    }


}