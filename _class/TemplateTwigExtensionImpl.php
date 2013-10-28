<?php
require_once dirname(__FILE__).'/TemplatePageElementTwigTokenParserImpl.php';
require_once dirname(__FILE__).'/TemplateInitPageElementTwigTokenParserImpl.php';
require_once dirname(__FILE__).'/TemplatePageContentTwigTokenParserImpl.php';
require_once dirname(__FILE__).'/TemplateSiteContentTwigTokenParserImpl.php';
/**
 * Created by PhpStorm.
 * User: budde
 * Date: 10/23/13
 * Time: 7:46 PM
 */

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
        return array(new TemplatePageElementTwigTokenParserImpl(),
                     new TemplateInitPageElementTwigTokenParserImpl(),
                     new TemplatePageContentTwigTokenParserImpl(),
                     new TemplateSiteContentTwigTokenParserImpl());
    }


}