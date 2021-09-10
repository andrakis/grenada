<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Module
 *
 * @author Daedalus
 */
abstract class Module {
    /**
     * List of pages the module handles.
     *
     * Each entry is in the form of:
     *    'requestToMatch'    =>   'nameOfClass'
     *
     * A root page is specified by '/'.
     * All pages should be in the form of '/page' and '/page/goes/here'
     *
     * You may use wildcards. Anything caught by the wildcards will be passed
     * into the Page's $parameters value.
     * Eg:
     *   'foo/*' will match 'foo/', 'foo/bar', 'foo/x'.
     *   What the * matches is passed into the Page's $parameters value.
     *
     * A catcher can be setup using '*' at the end of your pages array.
     */
    protected $pages = array(
        '*'     => 'ModuleNotConfigured',
    );

    /**
     * Apply any additional template blocks prior to page process
     * @param Template $t 
     */
    public function applyTemplateBlocks(Template $t)
    {

    }

    /**
     * Gets the template to be used for a Page request
     * @return Template
     */
    protected function getPageTemplate()
    {
        return new Template();
    }

    /**
     * Attempts to find a suitable page to handle the given request.
     * 
     * @see $pages
     * @param string $request Request URL
     * @return boolean Whether the page was handled by the module
     */
    final public function handlePageRequest($request)
    {
        foreach ($this->pages as $page => $class) {
            $asterix = strpos($page, '*');
            if ($asterix !== false) {
                // Check if everything preceding the * matches the request
                if (strncmp($request, $page, $asterix) != 0) continue; // Does not match
            } else {
                // Exact match required
                if (strcmp($request, $page) != 0) continue;             // Does not match
            }
            
            // Convert foo/*  into /foo\\/(.*)/  so that the wildcard is captured
            $paramsReg = '/'.str_replace(array('*', '/'), array('(.*)','\\/'), $page).'/';
            preg_match($paramsReg, $request, $matches);
            if (class_exists($class)) {
                $classInstance = new $class($matches[1], $this->getPageTemplate());
                Page::executePage($classInstance);
                return true;
            } else {
                throw new HandlerNotFoundException($class);
            }
        }
        
        // Not handled by this module
        return false;
    }
}
?>
