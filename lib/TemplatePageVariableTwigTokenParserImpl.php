<?php
namespace ChristianBudde\cbweb;
/**
 * Created by PhpStorm.
 * User: budde
 * Date: 1/31/14
 * Time: 11:08 PM
 */
use \Twig_TokenParser;
use \Twig_Token;
use \Twig_Node;
use \Twig_Error_Syntax;

class TemplatePageVariableTwigTokenParserImpl extends Twig_TokenParser{

    /**
     * Parses a token and returns a node.
     *
     * @param Twig_Token $token A Twig_Token instance
     *
     * @return Twig_Node A Twig_NodeInterface instance
     *
     * @throws Twig_Error_Syntax
     */
    public function parse(Twig_Token $token)
    {
        $stream = $this->parser->getStream();
        $page_id = "";
        $id = $stream->expect(Twig_Token::NAME_TYPE)->getValue();
        if($stream->getCurrent()->getType() == Twig_Token::PUNCTUATION_TYPE){
            $page_id = $id;
            $stream->expect(Twig_Token::PUNCTUATION_TYPE, "[");
            $id = $stream->expect(Twig_Token::NAME_TYPE)->getValue();
            $stream->expect(Twig_Token::PUNCTUATION_TYPE, "]");
        }
        $stream->expect(Twig_Token::BLOCK_END_TYPE);
        return new TemplatePageVariableTwigNodeImpl($token->getLine(), $this->getTag(), $id, $page_id);
    }

    /**
     * Gets the tag name associated with this token parser.
     *
     * @return string The tag name
     */
    public function getTag()
    {
        return "page_variable";
    }
}