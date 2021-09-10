<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of TemplateStackUnderflowException
 *
 * @author Daedalus
 */
class TemplateStackUnderflowException extends TemplateException {
    public function __construct() {
        parent::__construct('Stack underflow');
    }
}
?>
