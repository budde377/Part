<?php
namespace ChristianBudde\Part\view\template;
/**
 * Created by PhpStorm.
 * User: budde
 * Date: 1/31/14
 * Time: 11:10 PM
 */
use Twig_Compiler;
use Twig_Node;

class PageVariableTwigNodeImpl extends Twig_Node{
    /**
     * @param int $line
     * @param int $tag
     * @param \Twig_Node_Expression $id
     */
    function __construct($line, $tag, \Twig_Node_Expression $id)
    {
        parent::__construct(array('id'=>$id), [], $line, $tag);

    }

    public function compile(Twig_Compiler $compiler)
    {

        if ($this->getNode('id') == null) {
            $compiler->write("echo \$context['current_page']->getVariables()->getValue('');");
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
        $compiler->write("echo \${$pageIdVarName}->getVariables()->getValue(\$$idVarName);")->raw("\n");
        $compiler->write("} ")->raw("\n");
        $compiler->write("} else if(is_scalar(\$$inputVarName)){")->raw("\n");
        $compiler->write("echo \$context['current_page']->getVariables()->getValue(\$$inputVarName);")->raw("\n");
        $compiler->write("}")->raw("\n");


    }

} 