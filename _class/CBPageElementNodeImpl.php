<?php
/**
 * Created by PhpStorm.
 * User: budde
 * Date: 10/23/13
 * Time: 7:57 PM
 */

class CBPageElementNodeImpl extends Twig_Node{

    private $pageElementFactory;
    /**
     * @param PageElementFactory $pageElementFactory
     * @param string $name
     * @param int $line
     * @param int $tag
     */
    function __construct(PageElementFactory $pageElementFactory, $name, $line, $tag)
    {
        parent::__construct(array(), array('name'=>$name), $line, $tag);
        $this->pageElementFactory = $pageElementFactory;
    }

    public function compile(Twig_Compiler $compiler)
    {
        $name = $this->getAttribute('name');
        $pageElement = $this->pageElementFactory->getPageElement($name);
        if($pageElement == null){
            throw new Twig_Error("Could not find page element with name \"$name\"", $this->lineno);
        }
        $compiler->addDebugInfo($this)->write("echo ")->string($pageElement->generateContent())->write(";")->raw("\n");
    }


}