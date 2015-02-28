<?php
/**
 * Created by PhpStorm.
 * User: budde
 * Date: 2/27/15
 * Time: 6:30 PM
 */

namespace ChristianBudde\Part\test;


use ChristianBudde\Part\controller\ajax\type_handler\PostGetFilesArrayAccessTypeHandlerImpl;
use ChristianBudde\Part\controller\function_string\ParserImpl;

class PostGetFilesArrayAccessAJAXTypeHandlerImplTest extends \PHPUnit_Framework_TestCase{


    public function testListTypesListsAddedArrays()
    {
        $handler = new PostGetFilesArrayAccessTypeHandlerImpl();
        $this->assertEquals(["array", "POST", "GET", "FILES"], $handler->listTypes());
    }


    public function testGetPOSTGetsRightArray(){

        $f = (new ParserImpl())->parseString("POST.arrayAccess('id')")->toJSONProgram();
        $_POST['id'] = 2;
        $this->assertEquals(2, (new PostGetFilesArrayAccessTypeHandlerImpl())->handle('POST', $f));
    }

    public function testGetGETGetsRightArray(){

        $f = (new ParserImpl())->parseString("GET.arrayAccess('id')")->toJSONProgram();
        $_GET['id'] = 3;
        $this->assertEquals(3, (new PostGetFilesArrayAccessTypeHandlerImpl())->handle('GET', $f));
    }

    public function testGetFILESGetsRightArray(){

        $f = (new ParserImpl())->parseString("FILES.arrayAccess('id')")->toJSONProgram();
        $_FILES['id'] = 4;
        $this->assertEquals(4, (new PostGetFilesArrayAccessTypeHandlerImpl())->handle('FILES', $f));
    }
}