<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of FourOhFourPage
 *
 * @author Daedalus
 */
class FourOhFourPage extends Page {
    protected function execute(Template $t)
    {
        $t->setTemplate('404');
        $t->content = 'Page not found: '.$this->parameters;
        $t->title   = 'Page Not Found';
    }
}
?>
