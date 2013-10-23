<?php
/**
 * Created by PhpStorm.
 * User: budde
 * Date: 10/23/13
 * Time: 7:57 PM
 */

class CBPageContentNodeImpl extends Twig_Node{

    private $currentPage;

    /**
     * @param Page $currentPage
     * @param int $line
     * @param int $tag
     * @param string $id
     */
    function __construct(Page $currentPage, $line, $tag, $id = "")
    {
        parent::__construct(array(), array('id'=>$id), $line, $tag);
        $this->currentPage = $currentPage;
    }

    public function compile(Twig_Compiler $compiler)
    {
        $compiler->write("echo")->string($this->currentPage->getContent($this->getAttribute("id"))->latestContent())->write(";")->raw("\n");

    }


}