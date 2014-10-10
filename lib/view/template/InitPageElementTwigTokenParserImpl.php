<?php
namespace ChristianBudde\cbweb\view\template;

use \Twig_TokenParser;
use \Twig_Token;
use \Twig_Node;

/**
 * Created by PhpStorm.
 * User: budde
 * Date: 10/23/13
 * Time: 9:13 PM
 */

class InitPageElementTwigTokenParserImpl extends Twig_TokenParser
{

    /**
     * Parses a token and returns a node.
     *
     * @param Twig_Token $token A Twig_Token instance
     *
     * @return Twig_Node A Twig_NodeInterface instance
     */
    public function parse(Twig_Token $token)
    {
        $stream = $this->parser->getStream();
        $nameArray = [];
        $nameArray[] = $stream->expect(Twig_Token::NAME_TYPE)->getValue();
        while($stream->getCurrent()->getType() == Twig_Token::PUNCTUATION_TYPE){
            $stream->expect(Twig_Token::PUNCTUATION_TYPE);
            $nameArray[] = $stream->expect(Twig_Token::NAME_TYPE)->getValue();
        }
        $stream->expect(Twig_Token::BLOCK_END_TYPE);
        return new InitPageElementTwigNodeImpl($nameArray, $token->getLine(), $this->getTag());
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