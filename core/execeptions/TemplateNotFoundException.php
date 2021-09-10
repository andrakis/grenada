<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of TemplateNotFoundException
 *
 * @author Daedalus
 */
class TemplateNotFoundException extends TemplateException {
    public function __construct($template) {
        parent::__construct('Template not found: '.$template);
    }
}
?>
