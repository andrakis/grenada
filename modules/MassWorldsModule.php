<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of MassWorldsModule
 *
 * @author Daedalus
 */
class MassWorldsModule extends Module {
    protected $pages = array(
        '/'       => 'MassWorldsIndexPage',
        '/*'      => 'MassWorlds404Page',
        
    );

    protected $callables = array(
        'getTopBar'        => array('MassWorldsModule', 'tcGetTopBar')
    );

    protected function getPageTemplate()
    {
        $t = new Template();
        $t->setTemplate('massworlds');
        $t->topbar = process(new MassWorldsTopBarView());
        return $t;
    }
}
?>
