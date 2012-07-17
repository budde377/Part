<?php
require_once dirname(__FILE__) . '/../_interface/Logger.php';

/**
 * Created by JetBrains PhpStorm.
 * User: budde
 * Date: 6/3/12
 * Time: 9:38 PM
 * To change this template use File | Settings | File Templates.
 */
class LoggerImpl implements Logger
{

    private $rootPath;
    /** @var $file FileImpl */
    private $file = null;

    /**
     * @param string $rootPath
     */
    public function __construct($rootPath)
    {
        $this->rootPath = $rootPath;

    }

    /**
     * Logs message in specified
     * @param Object $caller
     * @param $message
     * @throws MalformedParameterException
     * @return void
     */
    public function log($caller, $message)
    {

        if (!is_object($caller)) {
            throw new MalformedParameterException('Object', 1);
        }
        if ($this->file === null) {
            $this->file = new FileImpl($this->rootPath . '/.log');
        }
        $callerName = get_class($caller);
        $time = date('Y-m-d H:i:s') . '.' . substr((string)microtime(), 1, 6);
        $this->file->write("$time: $callerName -> $message\r\n");
    }
}
