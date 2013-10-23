<?php
/**
 * Created by PhpStorm.
 * User: budde
 * Date: 10/23/13
 * Time: 7:57 PM
 */

class CBSiteContentNodeImpl extends Twig_Node{

    private $site;

    /**
     * @param Site $site
     * @param int $line
     * @param int $tag
     * @param null $id
     */
    function __construct(Site $site, $line, $tag, $id = null)
    {
        parent::__construct(array(), array('id'=>$id), $line, $tag);
        $this->site = $site;
    }

    public function compile(Twig_Compiler $compiler)
    {
        $compiler->write("echo")->string($this->site->getContent($this->getAttribute("id"))->latestContent())->write(";")->raw("\n");
    }


}