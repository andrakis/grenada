<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Default index page
 *
 *
 * @author Daedalus
 */
class IndexPage extends Page {
    public function __construct($parameters = null, $t = null)
    {
        if ($parameters !== null && $parameters != '/') throw new PageNotFoundException($parameters);

        parent::__construct($parameters, $t);
    }

    protected function execute(Template $t)
    {
        $t->content = process(Template::getPageTemplate('index.html'));
    }
}
?>
