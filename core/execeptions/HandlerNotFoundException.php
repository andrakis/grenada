<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of HandlerNotFoundException
 *
 * @author Daedalus
 */
class HandlerNotFoundException extends Exception {
    private $handlerName;

    public function __construct($handler = 'Unknown') {
        parent::__construct("Handler not found: $handler");
        $this->handlerName = $handler;
    }

    public function getHandlerName() {
        return $this->handlerName;
    }
}
?>
