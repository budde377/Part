<?php
/**
 * Created by PhpStorm.
 * User: budde
 * Date: 2/1/15
 * Time: 10:15 AM
 */

namespace ChristianBudde\cbweb\util\traits;


trait ParserTrait {
    protected function doubleQuotedStringToString($input)
    {
        $startCharacter = '"';
        $input = preg_replace("|([^\\\\])\\\\n|", "$1\n", $input);
        $input = preg_replace("/([^\\\\])\\\\r/", "$1\r", $input);
        $input = preg_replace("/([^\\\\])\\\\t/", "$1\t", $input);
        $input = preg_replace("/([^\\\\])\\\\v/", "$1\v", $input);
        $input = preg_replace("/([^\\\\])\\\\e/", "$1\e", $input);
        $input = preg_replace("/([^\\\\])\\\\f/", "$1\f", $input);
        $input = preg_replace_callback("/([^\\\\])\\\\([0-7]{1,3})/",
            function ($m) {
                return $m[1] . chr(octdec($m[2]));
            }, $input);
        $input = preg_replace_callback("/([^\\\\])\\\\x([0-9A-Fa-f]{1,2})/",
            function ($m) {
                return $m[1] . chr(hexdec($m[2]));
            }, $input);


        $input = preg_replace("/([^\\\\])\\\\$startCharacter/", "$1$startCharacter", $input);
        $input = str_replace("\\\\", "\\", $input);
        return substr($input, 1, strlen($input) - 2);

    }

    protected function singleQuotedStringToString($input)
    {

        $startCharacter = "'";
        $result = preg_replace("/([^\\\\])\\\\$startCharacter/", "$1$startCharacter", $input);
        $result = str_replace("\\\\", "\\", $result);
        return substr($result, 1, strlen($result) - 2);

    }

    /**
     * @param $a1
     * @param $a2
     * @return array
     */
    protected function merge_arrays($a1, $a2){

        foreach ($a2 as $key => $val) {
            $a1[$key] = $val;
        }

        return $a1;
    }
}