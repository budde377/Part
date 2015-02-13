<?php
namespace ChristianBudde\Part\view\template;
/**
 * Created by PhpStorm.
 * User: budde
 * Date: 10/23/13
 * Time: 7:46 PM
 */



use Twig_Extension;

class TemplateTwigExtensionImpl extends  Twig_Extension{



    /**
     * Returns the name of the extension.
     *
     * @return string The extension name
     */
    public function getName()
    {
        return "cb_template_extension";
    }

    public function getTokenParsers()
    {
        return array(new PageElementTwigTokenParserImpl(),
                     new InitPageElementTwigTokenParserImpl(),
                     new PageContentTwigTokenParserImpl(),
                     new SiteContentTwigTokenParserImpl(),
                     new SiteVariableTwigTokenParserImpl(),
                     new PageVariableTwigTokenParserImpl());
    }


}