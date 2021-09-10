<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of PageNotFoundException
 *
 * @author Daedalus
 */
class PageNotFoundException extends Exception {
    public function __construct($page = 'Unknown') {
        parent::__construct("Page not found: $page");
    }
}
?>
