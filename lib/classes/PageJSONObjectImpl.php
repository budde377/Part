<?php
/**
 * Created by JetBrains PhpStorm.
 * User: budde
 * Date: 24/01/13
 * Time: 09:25
 * To change this template use File | Settings | File Templates.
 */
class PageJSONObjectImpl extends JSONObjectImpl
{
    /**
     * @param String $id
     * @param string $title
     * @param string $template
     * @param string $alias
     * @param bool $hidden
     */
    function __construct($id,$title = '',$template = '',$alias='',$hidden=false)
    {
        parent::__construct('page');
        $this->setVariable('id',$id);
        $this->setVariable('title',$title);
        $this->setVariable('template',$template);
        $this->setVariable('alias',$alias);
        $this->setVariable('hidden',$hidden);
    }


}
