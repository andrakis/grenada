<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of GetContent
 *
 * @author Daedalus
 */
class GetContent extends Page {
    protected function execute(Template $t) {
        $t->setTemplate(TEMPLATES.'empty.html');

        // Read file
        $t->content = file_get_contents(BASE.'content/'.$this->parameters);
    }
}
?>
