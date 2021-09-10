<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of MassWorldsTopBarView
 *
 * @author Daedalus
 */
class MassWorldsTopBarView extends View {
    public function process()
    {
        $tpl = new Template(
                '<div id="topbar">{:menuItems}</div>'
                );
        $menuItems = array(
            'Home' => URL_ROOT,
            'Universe Map'  => URL_ROOT.'universe',
            'Galaxy Map'    => URL_ROOT.'galaxy',
            'Help'          => URL_ROOT.'help',
        );
        $menuHtml = '';
        foreach ($menuItems as $text => $url) {
            $menuHtml .= "<a href='$url'>$text</a>";
        }
        $tpl->menuItems = $menuHtml;

        return $tpl->process();
    }
}
?>
