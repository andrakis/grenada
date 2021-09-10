<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of UnknownTemplateCommandException
 *
 * @author Daedalus
 */
class UnknownTemplateCommandException extends TemplateException {
    public function __construct($command = 'Unknown') {
        parent::__construct('Unknown template command: '.$command);
    }
}
?>
