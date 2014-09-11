<?php
namespace ChristianBudde\cbweb;/**
 * Created by PhpStorm.
 * User: budde
 * Date: 8/28/14
 * Time: 2:00 PM
 */

class JSONParserImpl implements JSONParser{

    /**
     * @param string $input
     * @return JSONElement
     */
    public function parse($input)
    {

        $obj = json_decode($input, true);
        return $this->parseDecoded($obj);
    }

    private function parseDecoded($obj){

        if(!is_array($obj)){
            return $obj;
        }

        if(!isset($obj['type'])){
            $this->parseArray($obj);
            return $obj;
        }
        $type = $obj['type'];
        switch($type){
            case "type":
                if(!array_key_exists('type_string', $obj)){
                    break;
                }
                return new JSONTypeImpl($obj['type_string']);
                break;
            case "function":
                if(!$this->checkArrayKeysExists(['target','name','arguments','id'], $obj)){
                    break;
                }
                /** @var JSONTarget $target */
                $target = $this->parseDecoded($obj['target']);
                $function = new JSONFunctionImpl($obj['name'],$target );
                if($obj['id'] != null){
                    $function->setId($obj['id']);
                }
                foreach($obj['arguments'] as $key => $val){
                    $decodedVal = $this->parseDecoded($val);
                    $function->setArg($key, $decodedVal);
                }

                return $function;
                break;
            case "object":
                if(!$this->checkArrayKeysExists(['name', 'variables'], $obj)){
                    break;
                }

                $object = new JSONObjectImpl($obj['name']);
                foreach($obj['variables'] as $key => $val){
                    $object->setVariable($key, $this->parseDecoded($val));
                }
                return $object;
                break;
            case "response";
                if(!$this->checkArrayKeysExists(['response_type','error_code', 'payload', 'id'], $obj)){
                    break;
                }
                $response = new JSONResponseImpl($obj['response_type'], $obj['error_code']);
                $response->setPayload($this->parseDecoded($obj['payload']));
                return $response;
                break;
            case 'composite_function';
                if(!$this->checkArrayKeysExists(['functions', 'id', 'target'], $obj)){
                    break;
                }
                /** @var JSONTarget  $target */
                $target = $this->parseDecoded($obj['target']);
                $compositeFunction = new JSONCompositeFunctionImpl($target);
                foreach($obj['functions'] as $f){
                    /** @var JSONFunction $func */
                    $func = $this->parseDecoded($f);
                    $compositeFunction->appendFunction($func);
                }
                if($obj['id'] != null){
                    $compositeFunction->setId($obj['id']);
                }
                return $compositeFunction;

                break;

        }
        return $this->parseArray($obj);

    }

    /**
     * @return JSONElement
     */
    public function parseFromRequestBody()
    {
        $this->parse(file_get_contents("php://input"));
    }

    private function parseArray($obj)
    {
        foreach($obj as $key => $val){
            $obj[$key] = $this->parseDecoded($val);
        }
        return $obj;
    }

    private function checkArrayKeysExists($keys, $array){
        foreach($keys as $k){
            if(!array_key_exists($k, $array)){
                return false;
            }
        }
        return true;
    }
}