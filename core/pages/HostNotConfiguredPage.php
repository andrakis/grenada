<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of HostNotConfiguredPage
 *
 * @author Daedalus
 */
class HostNotConfiguredPage extends Page {
    protected function execute(Template $t)
    {
        $t->setTemplate(TEMPLATES.'error.html');
        $t->errorDescription = "Host {$_SERVER['SERVER_NAME']} not configured.";
    }
}
?>
