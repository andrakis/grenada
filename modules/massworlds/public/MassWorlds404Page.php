<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of MassWorlds404Page
 *
 * @author Daedalus
 */
class MassWorlds404Page extends Page {
    public function execute(Template $t)
    {
        $t->addContent('Page not found: '.$this->parameters);
        $t->title = '404';
    }
}
?>
