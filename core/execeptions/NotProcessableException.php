<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of NotProcessableException
 *
 * @author Daedalus
 */
class NotProcessableException extends Exception {
    public function __construct($class) {
        parent::__construct('Not a Processable class: '.$class);
    }
}
?>
