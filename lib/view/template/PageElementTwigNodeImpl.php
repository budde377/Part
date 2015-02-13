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
     * @param array $nameArray
     * @param int $line
     * @param int $tag
     */
    function __construct(array $nameArray, $line, $tag)
    {
        parent::__construct(array(), array('nameArray' => $nameArray), $line, $tag);

    }

    public function compile(Twig_Compiler $compiler)
    {
        $nameArray = $this->getAttribute('nameArray');
        $name = "";
        if(count($nameArray) == 1){
            $name = $nameArray[0];
        } else {
            foreach($nameArray as $n){
                $name.= "\\$n";
            }
        }

        $varName = uniqid("var");
        $compiler
            ->write("\$$varName = \$context['page_element_factory']->getPageElement('$name');")->raw("\n")
            ->write("if(\$$varName == null)")->raw("\n")
            ->write("throw new Twig_Error_Runtime('Could not find page element: \"$name\"', {$this->getLine()});")->raw("\n")
            ->write("else {")->raw("\n")
            ->write("if(!\${$varName}->hasBeenSetUp())")->raw("\n")
            ->write("\${$varName}->setUpElement();")->raw("\n")
            ->write("echo \${$varName}->generateContent();")->raw("\n")
            ->write("}")->raw("\n");
    }


}