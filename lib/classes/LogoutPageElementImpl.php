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

        if ($currentUser == null) {
            HTTPHeaderHelper::redirectToLocation("/");
            return;
        }

        $site = $this->container->getSiteInstance();
        $vars = $site->getVariables();
        $lastRun = $vars->getValue("last-file-lib-cleanup");
        $lastRun = $lastRun == null ? 0 : $lastRun;

        $fileLib = $this->container->getFileLibraryInstance();
        $contentLibraries = array();
        /** @var Page $page */
        foreach ($this->container->getPageOrderInstance()->listPages() as $page) {
            if ($page->lastModified() < $lastRun) {
                continue;
            }
            $contentLibraries[] = $page->getContentLibrary();

        }
        if($site->lastModified() >= $lastRun){
            $contentLibraries[] = $site->getContentLibrary();
        }
        $fileList = array();
        /** @var File $file */
        foreach ($fileLib->getFileList($currentUser) as $file) {
            if ($fileLib->whitelistContainsFile($file)) {
                continue;
            }
            /** @var $contentLib ContentLibrary */
            foreach ($contentLibraries as $contentLib) {
                if (!count($contentLib->searchLibrary($file->getBasename(), $lastRun))) {
                    continue;
                }
                $fileLib->addToWhitelist($file);
                break;
            }
            $fileList[] = $file;
        }
        $fileLib->cleanLibrary($currentUser);
        $vars->setValue("last-file-lib-cleanup", time());
        $currentUser->logout();

        HTTPHeaderHelper::redirectToLocation("/");
    }
}
