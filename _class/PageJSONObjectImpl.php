<?php
require_once dirname(__FILE__).'/JSONObjectImpl.php';
/**
 * Created by JetBrains PhpStorm.
 * User: budde
 * Date: 24/01/13
 * Time: 09:25
 * To change this template use File | Settings | File Templates.
 */
class PageJSONObjectImpl extends JSONObjectImpl
{
    function __construct($id,$title = '',$template = '',$alias='')
    {
        parent::__construct('page');
        $this->setVariable('id',$id);
        $this->setVariable('title',$title);
        $this->setVariable('template',$template);
        $this->setVariable('alias',$alias);

    }


}
