<?php
require_once dirname(__FILE__) . '/FileImpl.php';
require_once dirname(__FILE__) . '/../_interface/Template.php';
require_once dirname(__FILE__) . '/../_exception/FileNotFoundException.php';
require_once dirname(__FILE__) . '/../_exception/EntryNotFoundException.php';

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
        return $this->template;
    }


    /**
     * @param File $file
     * @throws FileNotFoundException
     * @return void
     */
    public function setTemplate(File $file)
    {

        if (!$file->fileExists()) {
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
        $numMatches = preg_match_all('/<!--[\s]*pageElement:([^\>]+)-->/', $this->template, $matches, PREG_OFFSET_CAPTURE);
        for ($i = $numMatches - 1; $i >= 0; $i--) {
            $elementString = trim($matches[1][$i][0]);
            /** @var $element PageElement */
            $element = $this->pageElementFactory->getPageElement($elementString);
            if ($element !== null) {
                $content = $element->getContent();
                $this->template = substr($this->template, 0, $matches[0][$i][1]) . $content . substr($this->template, $matches[0][$i][1] + strlen($matches[0][$i][0]));
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
