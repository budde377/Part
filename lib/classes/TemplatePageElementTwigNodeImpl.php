<?php

/**
 * Created by PhpStorm.
 * User: budde
 * Date: 10/23/13
 * Time: 7:57 PM
 */
class TemplatePageElementTwigNodeImpl extends Twig_Node
{

    /**
     * @param string $name
     * @param int $line
     * @param int $tag
     */
    function __construct($name, $line, $tag)
    {
        parent::__construct(array(), array('name' => $name), $line, $tag);

    }

    public function compile(Twig_Compiler $compiler)
    {
        $name = $this->getAttribute('name');
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