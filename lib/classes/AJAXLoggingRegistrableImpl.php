<?php
/**
 * Created by PhpStorm.
 * User: budde
 * Date: 5/17/14
 * Time: 4:07 PM
 */

class AJAXLoggingRegistrableImpl implements Registrable
{


    private $container;

    function __construct(BackendSingletonContainer $container)
    {
        $this->container = $container;
    }


    /**
     * @param $id string
     * @return string | null Will return string if id is found, else null
     */
    public function callback($id)
    {
        $server = new JSONServerImpl();
        $logFile = $this->container->getLogInstance();
        $server->registerJSONFunction(new JSONFunctionImpl("log", function($name, $stackTrace, $level) use ($logFile){
            $f = $logFile->log("AJAX Log", $level, true);
            $f->dumpVar("Name", $name);
            $f->dumpVar("Stack trace", $stackTrace);
            return new JSONResponseImpl(JSONResponse::RESPONSE_TYPE_SUCCESS);
        }, array("name", "stackTrace", "level")));

        $currentUser = $this->container->getUserLibraryInstance()->getUserLoggedIn();
        $siteUser = $currentUser != null && $currentUser->getUserPrivileges()->hasSitePrivileges();

        $server->registerJSONFunction(new JSONFunctionImpl("clearLog", function() use ($siteUser, $logFile) {
            if(!$siteUser){
                return new JSONResponseImpl(JSONResponse::RESPONSE_TYPE_ERROR, JSONResponse::ERROR_CODE_UNAUTHORIZED);
            }

            $logFile->clearLog();
            return new JSONResponseImpl();
        }));




        return $server->evaluatePostInput()->getAsJSONString();


    }
}