<?php
/**
 * Created by JetBrains PhpStorm.
 * User: budde
 * Date: 5/30/12
 * Time: 7:57 PM
 * To change this template use File | Settings | File Templates.
 */
class EntryNotFoundException extends Exception
{

    private $entry;
    private $context;

    /**
     * @param string $entry
     * @param string $context
     */
    public function __construct($entry, $context)
    {
        $this->entry = $entry;
        $this->context = $context;
        parent::__construct("EntryNotFoundException: No such entry:\"$entry\" in \"$context\"");
    }

    /**
     * @return string
     */
    public function getEntry()
    {
        return $this->entry;
    }

    /**
     * @return string
     */
    public function getContext()
    {
        return $this->context;
    }

}
