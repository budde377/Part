<?php
namespace ChristianBudde\Part\view\page_element;
use ChristianBudde\Part\BackendSingletonContainer;
use ChristianBudde\Part\Config;
use ChristianBudde\Part\model\user\User;
use ChristianBudde\Part\model\user\UserPrivileges;
use ChristianBudde\Part\view;


/**
 * Created by JetBrains PhpStorm.
 * User: budde
 * Date: 18/01/13
 * Time: 19:03
 */
class UserSettingsPageElementImpl extends PageElementImpl
{
    /** @var  Config */
    private $config;
    private $container;
    private $userLibrary;
    /** @var null|User  */
    private $currentUser;
    /** @var UserPrivileges */
    private $currentUserPrivileges;
    /** @var PageElement */
    private $editPagePageElement;
    /** @var PageElement */
    private $editPagesPageElement;
    /** @var PageElement */
    private $editUserPageElement;
    /** @var PageElement */
    private $editUsersPageElement;
    /** @var PageElement */
    private $editLogPageElement;
    /** @var PageElement */
    private $updateWebsitePageElement;


    function __construct(BackendSingletonContainer $container)
    {
        $this->container = $container;
        $this->userLibrary = $container->getUserLibraryInstance();
        $this->currentUser = $this->userLibrary->getUserLoggedIn();
        if ($this->currentUser != null) {
            $this->initialize();
        }
    }

    private function initialize()
    {
        $this->config = $this->container->getConfigInstance();
        $this->currentUserPrivileges = $this->currentUser->getUserPrivileges();
        $this->editPagePageElement = new UserSettingsEditPagePageElementImpl($this->container);
        $this->editPagesPageElement = new view\page_element\UserSettingsEditPagesPageElementImpl($this->container);
        $this->editUserPageElement = new view\page_element\UserSettingsEditUserPageElementImpl($this->container);
        $this->editUsersPageElement = new view\page_element\UserSettingsEditUsersPageElementImpl($this->container);
        if($this->currentUserPrivileges->hasRootPrivileges()){
            $this->editLogPageElement = new view\page_element\UserSettingsEditLogPageElementImpl($this->container);
        }
        if($this->currentUserPrivileges->hasSitePrivileges()){
            $this->updateWebsitePageElement = new UserSettingsUpdateWebsitePageElementImpl($this->container);
        }
    }

    /**
     * This will return content from page element as a string.
     * The format can be xml, xhtml, html etc. but return type must be string
     * @return string
     */
    public function generateContent()
    {
        parent::generateContent();
        if ($this->currentUser == null) {
            return "";
        }
        $output = "";
        $output .= $this->generateLoginUserMenuContainer();
        $output .= $this->generateLoginUserExpandMenuLink();
        $output .= $this->generateUpdateInformationMessage();
        $output .= $this->generateLoginUserMessage();
        return $output;
    }


    private function generateLoginUserExpandMenuLink()
    {
        return "
            <div id='UserSettingsExpandLink'>
                &nbsp;
            </div>";
    }

    private function generateUpdateInformationMessage()
    {
        $hidden = $this->container->getUpdater()->checkForUpdates(true)?"":"hidden";
        return "<div id='UpdateInformationMessage' $hidden>Siden kan opdateres. Klik <a href='#'>her</a> for at starte opdateringen.</div>";
    }

    private function generateLoginUserMessage()
    {
        return "
            <div id = 'LoginUserMessage'>
                Du er logget ind som <i>{$this->currentUser->getUsername()}</i>, <a href = '/logout'>log ud</a>.
            </div>";
    }


    private function generateLoginUserMenuContainer()
    {
        $currentPage = $this->container->getCurrentPageStrategyInstance()->getCurrentPage();
        $inactive = !$currentPage->isEditable() || !$this->currentUserPrivileges->hasPagePrivileges($currentPage);
        $inactiveClass = $inactive ? 'class="inactive"' : '';
        $inactiveFilter = $inactive ? "<div class='inactiveFilter' title='Du kan ikke redigere denne side'>&nbsp;</div>" : "";
        $updateElement = $updateLink = "";
        if($this->config->isUpdaterEnabled() && $this->currentUser->getUserPrivileges()->hasSitePrivileges()){
            $updateLink = "<li class='update_site' title='Opdater website'>&nbsp;</li>";
            $updateElement = "
                    <li>
                        <h2>Opdater Website</h2>
                        {$this->updateWebsitePageElement->generateContent()}
                    </li>
            ";

        }
        $logLink = $logElement = "";
        if($this->currentUser->getUserPrivileges()->hasRootPrivileges()){
            $logLink = "<li class='log ' title='Administrer log'>&nbsp;</li>";
            $logElement = "
                    <li>
                        <h2>Log</h2>
                        {$this->editLogPageElement->generateContent()}
                    </li>
            ";
        }
        $ret = "
        <div id='UserSettingsContainer'>
            <div id='UserSettingsMenu'>
                <ul>
                    <li class='page active' title='Rediger side'>&nbsp;</li>
                    <li class='pages' title='Administrer sider'>&nbsp;</li>
                    <li class='user ' title='Rediger oplysninger'>&nbsp;</li>
                    <li class='users' title='Administrer brugere'>&nbsp;</li>
                    $updateLink
                    $logLink
                    <!-- <li class='mail ' title='Administrer mailkonti'>&nbsp;</li>-->
                </ul>
            </div>
            <div id='UserSettingsContractLink'>&nbsp;</div>
            <div id='UserSettingsContent'>
                <ul >
                    <li $inactiveClass>
                        $inactiveFilter
                        <h2>Rediger Side</h2>
                        {$this->editPagePageElement->generateContent()}
                    </li>
                    <li>
                        <h2>Administrer Sider</h2>
                        {$this->editPagesPageElement->generateContent()}
                    </li>
                    <li>
                        <h2>Rediger Oplysninger</h2>
                        {$this->editUserPageElement->generateContent()}
                    </li>
                    <li>
                        <h2>Administrer Brugere</h2>
                        {$this->editUsersPageElement->generateContent()}
                    </li>
                    $updateElement
                    $logElement
                </ul>
            </div>
        </div>

        ";

        return $ret;
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


    }
}
