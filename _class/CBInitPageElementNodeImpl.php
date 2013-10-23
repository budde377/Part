<?php
/**
 * Created by PhpStorm.
 * User: budde
 * Date: 10/23/13
 * Time: 9:14 PM
 */

class CBInitPageElementNodeImpl extends Twig_Node
{
    private $pageElementFactory;

    /**
     * @param PageElementFactory $pageElementFactory
     * @param array $name
     * @param int $line
     * @param null|string $tag
     */
    public function __construct(PageElementFactory $pageElementFactory, $name, $line, $tag)
    {
        $this->pageElementFactory = $pageElementFactory;
        parent::__construct(array(), array('name'=>$name), $line, $tag);
    }

    public function compile(Twig_Compiler $compiler)
    {
        $name = $this->getAttribute('name');
        $pageElement = $this->pageElementFactory->getPageElement($name);
        if($pageElement == null){
            throw new Twig_Error("Could not find page element \"$name\"", $this->lineno);
        }
    }

} 