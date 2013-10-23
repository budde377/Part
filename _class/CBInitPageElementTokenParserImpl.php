<?php
require_once dirname(__FILE__) . '/CBInitPageElementNodeImpl.php';
/**
 * Created by PhpStorm.
 * User: budde
 * Date: 10/23/13
 * Time: 9:13 PM
 */

class CBInitPageElementTokenParserImpl extends Twig_TokenParser
{

    private $pageElementFactory;

    function __construct(PageElementFactory $pageElementFactory)
    {
        $this->pageElementFactory = $pageElementFactory;
    }


    /**
     * Parses a token and returns a node.
     *
     * @param Twig_Token $token A Twig_Token instance
     *
     * @return Twig_NodeInterface A Twig_NodeInterface instance
     */
    public function parse(Twig_Token $token)
    {
        $stream = $this->parser->getStream();
        $name = $stream->expect(Twig_Token::NAME_TYPE)->getValue();
        $stream->expect(Twig_Token::BLOCK_END_TYPE);
        return new CBInitPageElementNodeImpl($this->pageElementFactory, $name, $token->getLine(), $this->getTag());
    }

    /**
     * Gets the tag name associated with this token parser.
     *
     * @return string The tag name
     */
    public function getTag()
    {
        return "init_page_element";
    }
}