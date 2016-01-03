<?php
/**
 * Created by PhpStorm.
 * User: budde
 * Date: 2/27/15
 * Time: 6:30 PM
 */

namespace ChristianBudde\Part\controller\ajax\type_handler;



use ChristianBudde\Part\controller\function_string\ParserImpl;
use ChristianBudde\Part\controller\json\JSONFunction;

class PostGetFilesArrayAccessTypeHandlerImplTest extends \PHPUnit_Framework_TestCase{


    public function testListTypesListsAddedArrays()
    {
        $handler = new PostGetFilesArrayAccessTypeHandlerImpl();
        $this->assertEquals(["array", "POST", "GET", "FILES"], $handler->listTypes());
    }


    public function testGetPOSTGetsRightArray(){

        /** @var JSONFunction $f */
        $f = (new ParserImpl())->parseString("POST.arrayAccess('id')")->toJSONProgram();
        $_POST['id'] = 2;
        $this->assertEquals(2, (new PostGetFilesArrayAccessTypeHandlerImpl())->handle('POST', $f));
    }

    public function testGetGETGetsRightArray(){

        /** @var JSONFunction $f */
        $f = (new ParserImpl())->parseString("GET.arrayAccess('id')")->toJSONProgram();
        $_GET['id'] = 3;
        $this->assertEquals(3, (new PostGetFilesArrayAccessTypeHandlerImpl())->handle('GET', $f));
    }

    public function testGetFILESGetsRightArray(){

        /** @var JSONFunction $f */
        $f = (new ParserImpl())->parseString("FILES.arrayAccess('id')")->toJSONProgram();
        $_FILES['id'] = 4;
        $this->assertEquals(4, (new PostGetFilesArrayAccessTypeHandlerImpl())->handle('FILES', $f));
    }
}