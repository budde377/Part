<?php
namespace ChristianBudde\Part\view\template;
/**
 * Created by PhpStorm.
 * User: budde
 * Date: 10/23/13
 * Time: 9:13 PM
 */

use Twig_Node;
use Twig_Token;
use Twig_TokenParser;

class PageContentTwigTokenParserImpl extends Twig_TokenParser
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
        $id = $page_id = null;

        if($stream->getCurrent()->getType() != Twig_Token::BLOCK_END_TYPE){
            $id = $this->parser->getExpressionParser()->parseExpression();
        }

        $stream->expect(Twig_Token::BLOCK_END_TYPE);
        return new PageContentTwigNodeImpl($token->getLine(), $this->getTag(), $id);
    }

    /**
     * Gets the tag name associated with this token parser.
     *
     * @return string The tag name
     */
    public function getTag()
    {
        return "page_content";
    }
}