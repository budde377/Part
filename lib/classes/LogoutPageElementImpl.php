<?php

/**
 * Created by JetBrains PhpStorm.
 * User: budde
 * Date: 18/01/13
 * Time: 19:20
 */
class LogoutPageElementImpl extends PageElementImpl
{
    private $container;

    function __construct(BackendSingletonContainer $container)
    {
        $this->container = $container;
    }

    /**
     * Will set up the page element.
     * If you want to ensure that you register some files, this would be the place to do this.
     * This should always be called before generateContent, at the latest right before.
     * @return void
     */
    public function setUpElement()
    {
        parent::setUpElement();
        $currentUser = $this->container->getUserLibraryInstance()->getUserLoggedIn();

        if ($currentUser != null) {
            $vars = $this->container->getSiteInstance()->getVariables();
            $lastRun = $vars->getValue("last-file-lib-cleanup");
            $lastRun = $lastRun == null ? 0 : $lastRun;

            $fileLib = $this->container->getFileLibraryInstance();
            $fileList = array();
            $contentList = array();
            /** @var Page $page */
            foreach ($this->container->getPageOrderInstance()->listPages() as $page) {
                if ($page->lastModified() < $lastRun) {
                    continue;
                }
                $contentList = array_merge($contentList, array_values($page->getContentLibrary()->listContents($lastRun)));

            }
            $contentList = array_merge($contentList, array_values($this->container->getSiteInstance()->getContentLibrary()->listContents($lastRun)));


            /** @var File $file */
            foreach ($fileLib->getFileList($currentUser) as $file) {
                if ($fileLib->whitelistContainsFile($file)) {
                    continue;
                }
                /** @var $content Content */
                foreach($contentList as $content){
                    if(!$content->containsSubString($file->getFilename(),$lastRun)){
                        continue;
                    }
                    $fileLib->addToWhitelist($file);
                }
                $fileList[] = $file;
            }

            $fileLib->cleanLibrary($currentUser);
            $vars->setValue("last-file-lib-cleanup", time());
            $currentUser->logout();
        }
        HTTPHeaderHelper::redirectToLocation("/");
    }
}
