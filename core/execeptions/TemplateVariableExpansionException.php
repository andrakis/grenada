<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of TemplateVariableExpansionException
 *
 * @author Daedalus
 */
class TemplateVariableExpansionException extends TemplateException {
    public function __construct($params, $detail)
    {
        parent::__construct("Variable Expansion Exception: $detail<br/>".var_export($params,true));
    }
}
?>
