<?php
/**
 * Created by PhpStorm.
 * User: budde
 * Date: 8/28/14
 * Time: 3:44 PM
 */

class AJAXServerImpl implements AJAXServer{


    private $handlers = [];

    private $backendSingletonContainer;

    function __construct(BackendSingletonContainer $backendSingletonContainer)
    {
        $this->backendSingletonContainer = $backendSingletonContainer;
    }


    /**
     * Registers a AJAX type.
     * @param AJAXTypeHandler $type
     * @return void
     */
    public function registerHandler(AJAXTypeHandler $type)
    {
        $zeroLength = true;
        foreach($type->listTypes() as $t){
            $zeroLength = false;
            $this->handlers[$t][] = $type;
            $type->setUp($this, $t);
        }

        if($zeroLength){
            $type->setUp($this, null);
        }
    }

    /**
     * Registers the handlers from config.
     * @throws ClassNotDefinedException
     * @throws FileNotFoundException
     * @throws ClassNotInstanceOfException
     * @return void
     */
    public function registerHandlersFromConfig()
    {
        $config = $this->backendSingletonContainer->getConfigInstance();
        foreach($config->getAJAXTypeHandlers() as $handlerArray){

            if(isset($handlerArray['path'])){
                $path = $handlerArray['path'];
                if(!file_exists($path)){
                    throw new FileNotFoundException($path, "AJAXTypeHandler class file");
                }
                require_once $path;
            }

            $className = $handlerArray['class_name'];

            if(!class_exists($className)){
                throw new ClassNotDefinedException($className);
            }


            $handler = new $className($this->backendSingletonContainer);


            if(!($handler instanceof AJAXTypeHandler)){
                throw new ClassNotInstanceOfException($className, 'AJAXTypeHandler');
            }

            $this->registerHandler($handler);
        }
    }

    /**
     * @param string $input
     * @return string | null
     */
    public function handle($input)
    {
        // TODO: Implement handle() method.
    }

    /**
     * @return string | null
     */
    public function handleFromRequestBody()
    {
        // TODO: Implement handleFromRequestBody() method.
    }
}