<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Processable
 *
 * @author Daedalus
 */
interface Processable {
    /**
     * Process and return data
     * @return string
     */
    public function process();
}

/**
 * Calls the process function of the given variable and returns it's value
 * @param Implements Processable $x
 * @return string
 */
function process($x) {
    if ($x instanceOf Processable) {
        return $x->process();
    }

    throw new NotProcessableException(get_class($x));
}
?>
