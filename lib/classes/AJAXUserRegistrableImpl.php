<?php
/**
 * Created by JetBrains PhpStorm.
 * User: budde
 * Date: 9/6/13
 * Time: 12:12 AM
 * To change this template use File | Settings | File Templates.
 */

class AJAXUserRegistrableImpl implements Registrable{

    /** @var \BackendSingletonContainer  */
    private $container;
    /** @var null|\User  */
    private $currentUser;
    /** @var null|\UserPrivileges  */
    private $currentUserPrivileges;

    function __construct(BackendSingletonContainer $container)
    {
        $this->container = $container;
        $this->currentUser = $this->container->getUserLibraryInstance()->getUserLoggedIn();
        $this->currentUserPrivileges = $this->currentUser== null?null:$this->currentUser->getUserPrivileges();
    }

    /**
     * @param $id string
     * @return string | null Will return string if id is found, else null
     */
    public function callback($id)
    {


        $jsonServer = new JSONServerImpl();
        $userLibrary = $this->container->getUserLibraryInstance();
        $userTranslator = new UserJSONObjectTranslatorImpl($userLibrary);


        $loginFunction = new JSONFunctionImpl('userLogin',
            function ($username, $password) use ($userLibrary) {
                if(($user = $userLibrary->getUser($username)) == null){
                    return new JSONResponseImpl(JSONResponse::RESPONSE_TYPE_ERROR,
                        JSONResponse::ERROR_CODE_USER_NOT_FOUND);
                }
                if($user->login($password)){
                    return new JSONResponseImpl(JSONResponse::RESPONSE_TYPE_SUCCESS);
                }
                return new JSONResponseImpl(JSONResponse::RESPONSE_TYPE_ERROR,
                    JSONResponse::ERROR_CODE_WRONG_PASSWORD);

            },
            array('username', 'password'));
        $jsonServer->registerJSONFunction($loginFunction);


        $changeUserInfoFunction = new JSONFunctionImpl('changeUserInfo',
            function ($username, $newUsername, $mail) {
                if ($this->currentUser->getUsername() != $username) {
                    return new JSONResponseImpl(JSONResponse::RESPONSE_TYPE_ERROR, JSONResponse::ERROR_CODE_UNAUTHORIZED);
                }
                if ($username != $newUsername && !$this->currentUser->isValidUsername($newUsername)) {
                    return new JSONResponseImpl(JSONResponse::RESPONSE_TYPE_ERROR, JSONResponse::ERROR_CODE_INVALID_USER_NAME);
                }
                if (!$this->currentUser->isValidMail($mail)) {
                    return new JSONResponseImpl(JSONResponse::RESPONSE_TYPE_ERROR, JSONResponse::ERROR_CODE_INVALID_MAIL);
                }
                $this->currentUser->setUsername($newUsername);
                $this->currentUser->setMail($mail);
                return new JSONResponseImpl();
            }, array('username', 'new_username', 'mail'));
        $jsonServer->registerJSONFunction($changeUserInfoFunction);

        $changeUserPasswordFunction = new JSONFunctionImpl('changeUserPassword',
            function ($username, $oldPassword, $newPassword) {
                if ($this->currentUser->getUsername() != $username) {
                    return new JSONResponseImpl(JSONResponse::RESPONSE_TYPE_ERROR, JSONResponse::ERROR_CODE_UNAUTHORIZED);
                }
                if (!$this->currentUser->verifyLogin($oldPassword)) {
                    return new JSONResponseImpl(JSONResponse::RESPONSE_TYPE_ERROR, JSONResponse::ERROR_CODE_WRONG_PASSWORD);
                }
                if (!$this->currentUser->isValidPassword($newPassword)) {
                    return new JSONResponseImpl(JSONResponse::RESPONSE_TYPE_ERROR, JSONResponse::ERROR_CODE_INVALID_PASSWORD);
                }
                $this->currentUser->setPassword($newPassword);
                return new JSONResponseImpl();
            }, array('username', 'old_password', 'new_password'));
        $jsonServer->registerJSONFunction($changeUserPasswordFunction);


        $createUserFunction = new JSONFunctionImpl('createUser',
            function ($mail, $privileges) use ($userLibrary, $userTranslator) {
                if ($this->currentUserPrivileges == null) {
                    return new JSONResponseImpl(JSONResponse::RESPONSE_TYPE_ERROR, JSONResponse::ERROR_CODE_UNAUTHORIZED);
                }
                if ((!$this->currentUserPrivileges->hasRootPrivileges() &&
                        !$this->currentUserPrivileges->hasSitePrivileges()) ||
                    (!$this->currentUserPrivileges->hasRootPrivileges() && $privileges == 'root')
                ) {
                    return new JSONResponseImpl(JSONResponse::RESPONSE_TYPE_ERROR, JSONResponse::ERROR_CODE_UNAUTHORIZED);
                }
                if ($privileges != "root" && $privileges != "site" && $privileges != "page") {
                    return new JSONResponseImpl(JSONResponse::RESPONSE_TYPE_ERROR, JSONResponse::ERROR_CODE_INVALID_PRIVILEGES);
                }
                if (!$this->currentUser->isValidMail($mail)) {
                    return new JSONResponseImpl(JSONResponse::RESPONSE_TYPE_ERROR, JSONResponse::ERROR_CODE_INVALID_MAIL);
                }
                $username = explode('@', $mail);
                $username = $baseUsername = strtolower($username[0]);
                $i = 2;
                while (!$this->currentUser->isValidUsername($username)) {
                    $username = $baseUsername . '_' . $i;
                    $i++;
                }
                $password = uniqid();
                $user = $userLibrary->createUser($username, $password, $mail, $this->currentUser);
                if ($user == false) {
                    return new JSONResponseImpl(JSONResponse::RESPONSE_TYPE_ERROR);
                }
                $p = $user->getUserPrivileges();
                if ($privileges == 'root') {
                    $p->addRootPrivileges();
                } else if ($privileges == 'site') {
                    $p->addSitePrivileges();
                }
                // SEND MAIL TO USER
                $domain = $this->container->getConfigInstance()->getDomain();
                $m = new MailImpl();
                $m->addReceiver($user);
                $m->setSender("no-reply@$domain");
                $m->setMailType(Mail::MAIL_TYPE_PLAIN);
                $m->setSubject("Du er blevet oprettet som bruger på $domain");
                $m->setMessage("Hej,\n".
                "Du er blevet oprettet som bruger på $domain.\n".
                "Du kan logge ind med følgende oplysninger:\n\n".

                "    Brugernavn: {$user->getUsername()}\n".
                "    Kodeord:    $password\n\n".

                "Vh\n".
                "Admin Jensen");
                $m->sendMail();
                $response = new JSONResponseImpl();
                $response->setPayload($userTranslator->encode($user));
                return $response;

            }
            , array('mail', 'privileges'));
        $jsonServer->registerJSONFunction($createUserFunction);


        $deleteUserFunction = new JSONFunctionImpl('deleteUser',
            function ($username) use ($userLibrary) {
                if($this->currentUser == null){
                    return new JSONResponseImpl(JSONResponse::RESPONSE_TYPE_ERROR, JSONResponse::ERROR_CODE_UNAUTHORIZED);
                }

                $user = null;
                /** @var $u User */
                foreach ($userLibrary->getChildren($this->currentUser) as $u) {
                    if ($user == null && $u->getUsername() == $username) {
                        $user = $u;
                    }
                }
                if ($user == null) {
                    return new JSONResponseImpl(JSONResponse::RESPONSE_TYPE_ERROR, JSONResponse::ERROR_CODE_USER_NOT_FOUND);
                }
                $p = $userLibrary->getParent($user);
                while($p != null && $p != $this->currentUser){
                    $p = $userLibrary->getParent($p);
                }
                if($p == null){
                    return new JSONResponseImpl(JSONResponse::RESPONSE_TYPE_ERROR, JSONResponse::ERROR_CODE_UNAUTHORIZED);
                }
                $user->delete();
                return new JSONResponseImpl();
            }
            , array('username')
        );
        $jsonServer->registerJSONFunction($deleteUserFunction);




        return $jsonServer->evaluatePostInput()->getAsJSONString();
    }
}