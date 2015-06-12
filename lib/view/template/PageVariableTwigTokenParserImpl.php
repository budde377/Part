<?php
namespace ChristianBudde\Part\view\template;
/**
 * Created by PhpStorm.
 * User: budde
 * Date: 1/31/14
 * Time: 11:08 PM
 */

use Twig_Error_Syntax;
use Twig_Node;
use Twig_Token;
use Twig_TokenParser;

class PageVariableTwigTokenParserImpl extends Twig_TokenParser{

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
        $id = $page_id = null;

        if($stream->getCurrent()->getType() != Twig_Token::BLOCK_END_TYPE){
            $id = $this->parser->getExpressionParser()->parseExpression();
        }

        $stream->expect(Twig_Token::BLOCK_END_TYPE);
        return new PageVariableTwigNodeImpl($token->getLine(), $this->getTag(), $id);
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