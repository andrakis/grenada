<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of HandlerNotFoundPage
 *
 * @author Daedalus
 */
class HandlerNotFoundPage extends Page {
    protected function execute(Template $t)
    {
        $t->content = 'Handler not found: '.$this->parameters;
    }
}
?>
