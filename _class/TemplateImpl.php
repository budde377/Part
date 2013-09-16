<?php
require_once dirname(__FILE__) . '/FileImpl.php';
require_once dirname(__FILE__) . '/../_interface/Template.php';
require_once dirname(__FILE__) . '/../_exception/FileNotFoundException.php';
require_once dirname(__FILE__) . '/../_exception/EntryNotFoundException.php';
require_once dirname(__FILE__) . '/../_helper/HTTPHeaderHelper.php';

/**
 * Created by JetBrains PhpStorm.
 * User: budde
 * Date: 5/15/12
 * Time: 8:31 AM
 */
class TemplateImpl implements Template
{

    private $config;
    private $pageElementFactory;
    private $backendContainer;
    /** @var  DOMDocument */
    private $template;
    private $pageElements = array();


    /**
     * @param PageElementFactory $pageElementFactory
     * @param BackendSingletonContainer $container
     * @internal param \Config $config
     */

    public function __construct(PageElementFactory $pageElementFactory, BackendSingletonContainer $container)
    {
        $this->config = $container->getConfigInstance();
        $this->pageElementFactory = $pageElementFactory;
        $this->backendContainer = $container;
    }

    private function cleanUp($string){


        return $string;
    }

    /**
     * Will return the modified template, hence the template with possibly added page elements.
     * The return type must be of type string, but can be HTML, XHTML, JSON, etc.
     * @throws FileNotFoundException
     * @return string
     */
    public function getModifiedTemplate()
    {

        // Building output
        $query = $this->xPathOnTemplate("//cb:page-element");
        /** @var $pageElement DOMElement */
        foreach ($query as $pageElement) {
            $newNode = $this->template->createDocumentFragment();
            /** @var PageElement $pe */
            $pe = $this->pageElements[$pageElement->getAttribute('name')];
            if($pe == null){
                continue;
            }
            $newNode->appendXML("<![CDATA[".str_replace('>','&gt;', str_replace('<','&lt;',$pe->getContent()))."]]>");
            $pageElement->parentNode->replaceChild($newNode, $pageElement);
        }

        // Getting content
        $contentQuery = $this->xPathOnTemplate("//cb:page-content");
        if($contentQuery->length > 0){
            $currentPage = $this->backendContainer->getCurrentPageStrategyInstance()->getCurrentPage();
            /** @var $pageContent DOMElement */
            foreach($contentQuery as $pageContent){
                $content = $currentPage->getContent($pageContent->hasAttribute('id')?$pageContent->getAttribute('id'):null);
                $fragment = $this->template->createDocumentFragment();
                $fragment->appendXML("<![CDATA[".str_replace('>','&gt;', str_replace('<','&lt;',$content->latestContent()))."]]>");
                if($fragment->childNodes->length == 0){
                    continue;
                }
                $pageContent->parentNode->insertBefore($fragment,$pageContent);
                $pageContent->parentNode->removeChild($pageContent);

            }

        }
        /** @var $pe DOMElement */
        foreach($this->xPathOnTemplate('//cb:*') as $pe){
            $pe->parentNode->removeChild($pe);
        }

        $result = $this->template->saveXML();
        // Remove xmlns
        $pattern = '/(<[^>]+)[\s]*xmlns(:?[a-z]*)\s*=\s*"[^"]*"[\s]*([^>]*>)/';
        while(preg_match($pattern, $result)){
            $result = preg_replace($pattern,'$1 $3', $result);
        }

        // Remove prefix
        $result = preg_replace("/(<\/?)[^:>\s]+:([^>]+>)/", "$1$2", $result);

        // Remove xml declaration
        $result = preg_replace("/^<\?[^>]+>\s*/", "", $result);

        // Remove CDATA blocks
        preg_match_all('/<!\[CDATA\[([^>]*)\]\]>/', $result, $matches, PREG_OFFSET_CAPTURE);
        for($i = count($matches[0])-1; $i >= 0; $i--){

            $match = $matches[0][$i];
            $result = substr($result, 0, $match[1]).str_replace("&lt;", "<", str_replace("&gt;", ">", $matches[1][$i][0])).substr($result, $match[1]+strlen($match[0]));
        }

        return $result;
    }


    private function xPathOnTemplate($xpath)
    {
        $pageElementsXPath = new DOMXPath($this->template);
        $pageElementsXPath->registerNamespace("cb", "http://christianbud.de/template");
        return $pageElementsXPath->query($xpath);

    }

    /**
     * @param File $file
     * @throws FileNotFoundException
     * @return void
     */
    public function setTemplate(File $file)
    {

        if (!$file->exists()) {
            throw new FileNotFoundException($file->getAbsoluteFilePath(), 'template file');
        }
        $templateString = $file->getContents();
        $this->setTemplateFromString($templateString);
    }

    private function evaluateCondition($expression)
    {
        $backendContainer = $this->backendContainer;
        $result = eval("return $expression;");
        return $result;
    }

    /**
     * @param string $string The template as a string
     * @throws InvalidXMLException
     * @return void
     */
    public function setTemplateFromString($string)
    {
        //Cleaning up and building template
        try {

            $string = html_entity_decode($string);
            $this->template = new DOMDocument();
            $this->template->loadXML($string);

        } catch (Exception $e) {
            throw new InvalidXMLException();
        }

        // Manage condition elements
        $conditions = $this->xPathOnTemplate('//cb:condition');
        /** @var DOMElement $condition */
        foreach ($conditions as $condition) {
            $parent = $condition->parentNode;
            if ($this->evaluateCondition($condition->getAttribute('expression'))) {
                $fragment = $this->template->createDocumentFragment();
                while($condition->childNodes->length){
                    $fragment->appendChild($condition->childNodes->item(0));
                }
                $condition->parentNode->insertBefore($fragment, $condition);

            }
            $parent->removeChild($condition);
        }

        // Remove page-element tags with false condition
        $conditionPageElements = $this->xPathOnTemplate("//cb:page-element[@condition]");
        /** @var DOMElement $conditionalPE */
        foreach ($conditionPageElements as $conditionalPE) {
            if ($this->evaluateCondition($conditionalPE->getAttribute('condition'))) {
                continue;
            }
            $conditionalPE->parentNode->removeChild($conditionalPE);
        }

        // Resolve extension
        $extendsMatches = $this->xPathOnTemplate("/cb:extend-template");
        if ($extendsMatches->length > 0) {

            /** @var DOMElement $match */
            $match = $extendsMatches->item(0);
            $f = new FileImpl($match->getAttribute("url"));
            $matches = $this->xPathOnTemplate("/*/cb:replace-page-element");
            $this->setTemplate($f);
            /** @var $m DOMElement */
            foreach ($matches as $m) {
                $pageElements = $this->xPathOnTemplate($s = "//cb:page-element[@name=\"{$m->getAttribute('name')}\"]");
                if ($pageElements->length == 0) {
                    continue;
                }
                /** @var DOMElement $firstMatch */
                $firstMatch = $pageElements->item(0);
                $newChildren = $this->template->createDocumentFragment();
                foreach ($m->childNodes as $child) {
                    $newChildren->appendChild($this->template->importNode($child, true));
                }
                $firstMatch->parentNode->replaceChild($newChildren, $firstMatch);


            }
        }

        //Initialize page elements
        $finalPageElements = $this->xPathOnTemplate("//cb:page-element[@name]");
        /** @var $attr DOMElement */
        foreach ($finalPageElements as $attr) {
            $attrString = $attr->getAttribute("name");
            if (isset($this->pageElements[$attrString])) {
                continue;
            }
            $this->pageElements[$attrString] = $this->pageElementFactory->getPageElement($attrString);

        }

    }

    /**
     * @param string $name The name of the template as defined in the config
     * @throws EntryNotFoundException
     * @return void
     */
    public function setTemplateFromConfig($name)
    {
        $link = $this->config->getTemplate($name);
        if ($link === null) {
            throw new EntryNotFoundException($name, 'Config');
        }
        $file = new FileImpl($link);
        $this->setTemplate($file);
    }
}
