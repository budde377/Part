<?php
require_once dirname(__FILE__) . '/../_interface/PageElement.php';
require_once dirname(__FILE__) . '/../_interface/Registrable.php';
require_once dirname(__FILE__) . '/DartFileImpl.php';
require_once dirname(__FILE__) . '/CSSFileImpl.php';
require_once dirname(__FILE__) . '/JSONServerImpl.php';
require_once dirname(__FILE__) . '/JSONFunctionImpl.php';
require_once dirname(__FILE__) . '/PageJSONObjectTranslatorImpl.php';
require_once dirname(__FILE__) . '/UserJSONObjectTranslatorImpl.php';
require_once dirname(__FILE__) . '/UserSettingsEditPagePageElementImpl.php';
require_once dirname(__FILE__) . '/UserSettingsEditPagesPageElementImpl.php';
require_once dirname(__FILE__) . '/UserSettingsEditUserPageElementImpl.php';
require_once dirname(__FILE__) . '/UserSettingsEditUsersPageElementImpl.php';
require_once dirname(__FILE__) . '/UserSettingsUpdateWebsitePageElementImpl.php';
/**
 * Created by JetBrains PhpStorm.
 * User: budde
 * Date: 18/01/13
 * Time: 19:03
 */
class UserSettingsPageElementImpl implements PageElement
{
    /** @var  Config */
    private $config;
    private $container;
    private $userLibrary;
    /** @var null|\User  */
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
        $this->editPagesPageElement = new UserSettingsEditPagesPageElementImpl($this->container);
        $this->editUserPageElement = new UserSettingsEditUserPageElementImpl($this->container);
        $this->editUsersPageElement = new UserSettingsEditUsersPageElementImpl($this->container);
        $this->updateWebsitePageElement = new UserSettingsUpdateWebsitePageElementImpl($this->container);

        $cssRegister = $this->container->getCSSRegisterInstance();
        $cssRegister->registerCSSFile(new CSSFileImpl(dirname(__FILE__) . '/../_css/userSettingsStyle.css'));
    }

    /**
     * This will return content from page element as a string.
     * The format can be xml, xhtml, html etc. but return type must be string
     * @return string
     */
    public function getContent()
    {
        if ($this->currentUser == null) {
            return "";
        }
        $output = "";
        $output .= $this->generateLoginUserMenuContainer();
        $output .= $this->generateLoginUserExpandMenuLink();
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

    private function generateLoginUserMessage()
    {
        return "
            <div id = 'LoginUserMessage'>
                Du er logget ind som <i>{$this->currentUser->getUsername()}</i>, <a href = '/logout'>log ud</a>.
            </div>";
    }

    private function generateLoginUserMenuContainer()
    {
        $expanded = array('', '', '', '', '','', '');
        $marginLeft = "";
        if (isset($_COOKIE['expandSetting']) && ($i = intval($_COOKIE['expandSetting'])) < 5 && $i >= 0) {
            $expanded[0] = 'class="expanded"';
            $expanded[$i + 1] = 'active';
            setcookie('expandSetting', '-1', time()-3600, '/');
            $m = $i * -880;
            $marginLeft = "margin-left:{$m}px;";
        } else {
            $expanded[1] = 'active';
        }
        $currentPage = $this->container->getCurrentPageStrategyInstance()->getCurrentPage();
        $inactive = !$currentPage->isEditable() || !$this->currentUserPrivileges->hasPagePrivileges($currentPage);
        $inactiveClass = $inactive ? 'class="inactive"' : '';
        $inactiveFilter = $inactive ? "<div class='inactiveFilter' title='Du kan ikke redigere denne side'>&nbsp;</div>" : "";
        $updateElement = $updateLink = "";
        if($this->config->isUpdaterEnabled() && $this->currentUser->getUserPrivileges()->hasSitePrivileges()){
            $updateLink = "<li class='update_site {$expanded[5]}' title='Opdater website'>&nbsp;</li>";
            $updateElement = "
                    <li>
                        <h2>Opdater Website</h2>
                        {$this->updateWebsitePageElement->getContent()}
                    </li>
            ";

        }
        $ret = "
        <div id='UserSettingsContainer' {$expanded[0]}>
            <div id='UserSettingsMenu'>
                <ul>
                    <li class='page {$expanded[1]}' title='Rediger side'>&nbsp;</li>
                    <li class='pages {$expanded[2]}' title='Administrer sider'>&nbsp;</li>
                    <li class='user {$expanded[3]}' title='Rediger oplysninger'>&nbsp;</li>
                    <li class='users {$expanded[4]}' title='Administrer brugere'>&nbsp;</li>
                    $updateLink
<!--                    <li class='mail {$expanded[6]}' title='Administrer mailkonti'>&nbsp;</li>-->
                </ul>
            </div>
            <div id='UserSettingsContractLink'>&nbsp;</div>
            <div id='UserSettingsContent'>
                <ul style='$marginLeft'>
                    <li $inactiveClass>
                        $inactiveFilter
                        <h2>Rediger Side</h2>
                        {$this->editPagePageElement->getContent()}
                    </li>
                    <li>
                        <h2>Administrer Sider</h2>
                        {$this->editPagesPageElement->getContent()}
                    </li>
                    <li>
                        <h2>Rediger Oplysninger</h2>
                        {$this->editUserPageElement->getContent()}
                    </li>
                    <li>
                        <h2>Administrer Brugere</h2>
                        {$this->editUsersPageElement->getContent()}
                    </li>
                    $updateElement

                </ul>
            </div>
        </div>

        ";

        return $ret;
    }


}
