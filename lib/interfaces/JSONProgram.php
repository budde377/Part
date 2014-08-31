<?php
/**
 * Created by PhpStorm.
 * User: budde
 * Date: 8/31/14
 * Time: 4:02 PM
 */

interface JSONProgram extends JSONElement{


    /**
     * @param JSONTarget $target
     * @return void
     */
    public function setTarget(JSONTarget $target);

    /**
     * @return JSONTarget
     */
    public function getTarget();

    /**
     * @return int
     */
    public function getId();

    /**
     * @param int $id
     * @return mixed
     */
    public function setId($id);

} 