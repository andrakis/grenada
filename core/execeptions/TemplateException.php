<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of TemplateException
 *
 * @author Daedalus
 */
class TemplateException extends Exception {
    public function __construct($message) {
        parent::__construct($message);
    }

    public function getTemplateExceptionDetails() {
        return 'Token index: '.Template::$currentTokenIndex."\n".
               'Token: '.Template::$currentTokens[Template::$currentTokenIndex]."\n".
               'Tokens: '.var_export(Template::$currentTokens, true)."\n".
               'Template: '.var_export(Template::$currentTemplateString, true);
    }
}
?>
