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
    private $template;
    private $pageElements = array();


    /**
     * @param Config $config
     * @param PageElementFactory $pageElementFactory
     */

    public function __construct(Config $config, PageElementFactory $pageElementFactory)
    {
        $this->config = $config;
        $this->pageElementFactory = $pageElementFactory;
    }

    /**
     * Will return the modified template, hence the template with possibly added page elements.
     * The return type must be of type string, but can be HTML, XHTML, JSON, etc.
     * @throws FileNotFoundException
     * @return string
     */
    public function getModifiedTemplate()
    {
        foreach($this->pageElements as $array){
            /** @var $element PageElement */
            $element = $array['element'];
            $this->template = substr($this->template, 0, $array['offset_start']) . $element->getContent() . substr($this->template, $array['offset_end']);
        }
        return $this->template;
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

    /**
     * @param string $string The template as a string
     * @return void
     */
    public function setTemplateFromString($string)
    {

        $this->template = $string;
        if(preg_match('/<!--[\s]*headerStatusCode:([0-9]+)-->/', $this->template, $matches,PREG_OFFSET_CAPTURE)){
            $statusCode = $matches[1][0];
            $this->template = substr($this->template,0,$matches[0][1]).substr($this->template, $matches[0][1]+strlen($matches[0][0]));
            switch($statusCode){
                case 500:
                    HTTPHeaderHelper::setHeaderStatusCode(HTTPHeaderHelper::HTTPHeaderStatusCode500);
                    break;
                case 200:
                    HTTPHeaderHelper::setHeaderStatusCode(HTTPHeaderHelper::HTTPHeaderStatusCode200);
                    break;
                case 404:
                    HTTPHeaderHelper::setHeaderStatusCode(HTTPHeaderHelper::HTTPHeaderStatusCode404);

            }
        }


        $numExtendsMatch = preg_match_all('/<!--[\s]*extends:([^\>]+)-->/', $string, $matches, PREG_OFFSET_CAPTURE);
        if ($numExtendsMatch > 0) {
//            $template = new TemplateImpl($this->config, $this->pageElementFactory);
            $f = new FileImpl(trim($matches[1][0][0]));
            $this->template = $f->getContents();

            $numStartMatches = preg_match_all('/<!--[\s]*replaceElementStart\[([^\]]+)\][\s]*-->/', $string, $startMatches, PREG_OFFSET_CAPTURE);
            $numEndMatches = preg_match_all('/<!--[\s]*replaceElementEnd[\s]*-->/', $string, $endMatches, PREG_OFFSET_CAPTURE);
            if($numStartMatches == $numEndMatches){
                $tagConflicts = false;
                foreach($startMatches[0] as $k=>$match){
                    $tagConflicts = $tagConflicts || $match[1] > $endMatches[0][$k][1];
                    if(!$tagConflicts){
                        $start = $match[1]+strlen($match[0]);
                        $c =  substr($string,$start,$endMatches[0][$k][1]-$start);
                        $this->template = preg_replace("/<!--[\s]*pageElement:[\s]*{$startMatches[1][$k][0]}[\s]*-->/",$c,$this->template);
                    }
                }
            }
            preg_match_all('/<!--[\s]*replaceElement\[([^\]]+)\]:([^\>]+)-->/', $string, $matches, PREG_OFFSET_CAPTURE);
            foreach ($matches[1] as $k => $match) {
                $replaceName = trim($match[0]);
                $this->template = preg_replace("/(<!--[\s]*pageElement:[\s]*){$replaceName}([\s]*-->)/","$1{$matches[2][$k][0]}$2",$this->template);
            }

        }

        $numMatches = preg_match_all('/<!--[\s]*pageElement:([^\>]+)-->/', $this->template, $matches,PREG_OFFSET_CAPTURE);
        for($i = $numMatches -1; $i >=0;$i--){
            $elementString = trim($matches[1][$i][0]);
            if (($element = $this->pageElementFactory->getPageElement($elementString)) !== null) {
                /** @var $element PageElement */
                $this->pageElements[] = array('element'=>$element,'offset_start'=>$matches[0][$i][1], 'offset_end' => $matches[0][$i][1] + strlen($matches[0][$i][0]));
             }
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
