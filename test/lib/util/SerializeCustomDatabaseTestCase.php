<?php
/**
 * Created by PhpStorm.
 * User: budde
 * Date: 7/2/15
 * Time: 4:58 PM
 */

namespace ChristianBudde\Part\test\util;


class SerializeCustomDatabaseTestCase extends CustomDatabaseTestCase {

    private $instance;

    function __construct($dataset, &$instance)
    {
        parent::__construct($dataset);
        $this->instance = &$instance;
    }

    protected function tearDown()
    {
        parent::tearDown();
        $this->assertEquals(serialize($this->instance), serialize(unserialize(serialize($this->instance))));

    }


}