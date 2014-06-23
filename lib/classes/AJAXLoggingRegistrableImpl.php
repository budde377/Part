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
        $user = $this->container->getUserLibraryInstance()->getUserLoggedIn();
        $server = new JSONServerImpl();
        $logFile = $this->container->getLogInstance();
        $server->registerJSONFunction(new JSONFunctionImpl("log", function($name, $stackTrace, $level) use ($logFile, $user){
            $f = $logFile->log("AJAX Log", $level, true);
            $f->dumpVar("User", $user==null?null:$user->getUsername());
            $f->dumpVar("Name", $name);
            $f->dumpVar("Stack trace", $stackTrace);
            return new JSONResponseImpl(JSONResponse::RESPONSE_TYPE_SUCCESS);
        }, array("name", "stackTrace", "level")));

        $currentUser = $this->container->getUserLibraryInstance()->getUserLoggedIn();
        $siteUser = $currentUser != null && $currentUser->getUserPrivileges()->hasSitePrivileges();

        $server->registerJSONFunction(new JSONFunctionImpl("clear", function() use ($siteUser, $logFile) {
            if(!$siteUser){
                return new JSONResponseImpl(JSONResponse::RESPONSE_TYPE_ERROR, JSONResponse::ERROR_CODE_UNAUTHORIZED);
            }

            $logFile->clearLog();
            return new JSONResponseImpl();
        }));

        $server->registerJSONFunction(new JSONFunctionImpl("getDumpFile", function($id) use($siteUser, $logFile){
            if(!$siteUser){
                return new JSONResponseImpl(JSONResponse::RESPONSE_TYPE_ERROR, JSONResponse::ERROR_CODE_UNAUTHORIZED);
            }
            $l = $logFile->listLog(LogFile::LOG_LEVEL_ALL, $id);
            if(!count($l) || !isset($l[0]["dumpfile"])){
                return new JSONResponseImpl(JSONResponse::RESPONSE_TYPE_ERROR, JSONResponse::ERROR_CODE_FILE_NOT_FOUND);
            }
            /** @var DumpFile $df */
            $df = $l[0]["dumpfile"];
            $resp = new JSONResponseImpl();
            $resp->setPayload($df->getContents());
            return $resp;
        }, array("id")));


        return $server->evaluatePostInput()->getAsJSONString();


    }
}