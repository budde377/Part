<?php
require_once dirname(__FILE__) . '/../../_interface/Content.php';
/**
 * Created by JetBrains PhpStorm.
 * User: budde
 * Date: 9/15/13
 * Time: 10:41 PM
 * To change this template use File | Settings | File Templates.
 */

class StubContentImpl implements Content{

    private $content = array();

    /**
     * @param int | null $from List history from a specific time. If null the whole history will be returned.
     * @param int| null $to List history to a specific time.
     * @return array An array containing arrays with keys: "time" and "content"
     */
    public function listContentHistory($from = null, $to = null)
    {
        return $this->content;
    }

    /**
     * @return string | null Returns the latest content as a string or null if no content exists.
     */
    public function latestContent()
    {
        end($this->content);
        return current($this->content)['content'];

    }

    /**
     * @return int | null Returns the time of latest content as timestamp since epoc. If no content, then return null;
     */
    public function latestTime()
    {
        end($this->content);
        return current($this->content)['time'];
    }

    /**
     * @param string $content Adds new content. This will be the latest upon addition.
     * @return int | null Returns null on error else the latest time
     */
    public function addContent($content)
    {
        $t = time();
        $this->content[] = array('time'=>$t, 'content'=>$content);
        return $t;
    }

    /**
     * @param int $time Seconds since epoch
     * @return Array | null Returns content at time or null if no content
     */
    public function getContentAt($time)
    {
        $content = null;
        foreach($this->content as $c){
            if($c['time']> $time){
                return $content;
            }
            $content = $c;
        }
        return $content;
    }
}